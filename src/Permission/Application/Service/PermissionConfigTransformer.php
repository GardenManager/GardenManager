<?php

declare(strict_types=1);

namespace GardenManager\Permission\Application\Service;

use GardenManager\Permission\Domain\Exception\PermissionException;
use GardenManager\Tenant\Application\Port\MemberUserResolverInterface;
use Symfony\Component\Uid\Ulid;

final readonly class PermissionConfigTransformer
{
    public function __construct(
        private MemberUserResolverInterface $memberUserResolver,
    ) {
    }

    /**
     * Replaces ULID keys in userAssignments and userOverrides with email addresses
     * for human-readable display in the raw editor.
     *
     * @param array<string, mixed> $configArray
     *
     * @return array<string, mixed>
     */
    public function replaceUlidKeysWithEmails(array $configArray): array
    {
        $ulidKeys = [];

        foreach (['userAssignments', 'userOverrides'] as $section) {
            foreach (array_keys($configArray[$section] ?? []) as $key) {
                if (Ulid::isValid((string) $key)) {
                    $ulidKeys[] = new Ulid((string) $key);
                }
            }
        }

        if ($ulidKeys === []) {
            return $configArray;
        }

        $resolved = $this->memberUserResolver->resolveByIds($ulidKeys);

        foreach (['userAssignments', 'userOverrides'] as $section) {
            if (!isset($configArray[$section])) {
                continue;
            }

            $replaced = [];

            foreach ($configArray[$section] as $key => $value) {
                $ulidStr = (string) $key;

                if (isset($resolved[$ulidStr])) {
                    $replaced[$resolved[$ulidStr]->email] = $value;
                } else {
                    $replaced[$ulidStr] = $value;
                }
            }

            $configArray[$section] = $replaced;
        }

        return $configArray;
    }

    /**
     * Replaces email keys in userAssignments and userOverrides with ULIDs
     * for storage. Throws PermissionException if any email cannot be resolved.
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     *
     * @throws PermissionException when any email key cannot be resolved to a user
     */
    public function replaceEmailKeysWithUlids(array $data): array
    {
        $errors = [];

        foreach (['userAssignments', 'userOverrides'] as $section) {
            if (!isset($data[$section]) || !is_array($data[$section])) {
                continue;
            }

            $replaced = [];

            foreach ($data[$section] as $key => $value) {
                $keyStr = (string) $key;

                if (str_contains($keyStr, '@')) {
                    $info = $this->memberUserResolver->resolveByEmail($keyStr);

                    if ($info === null) {
                        $errors[] = \sprintf('User "%s" not found.', $keyStr);
                        $replaced[$keyStr] = $value;
                    } else {
                        $replaced[(string) $info->id] = $value;
                    }
                } else {
                    $replaced[$keyStr] = $value;
                }
            }

            $data[$section] = $replaced;
        }

        if ($errors !== []) {
            throw PermissionException::invalidConfig($errors);
        }

        return $data;
    }
}
