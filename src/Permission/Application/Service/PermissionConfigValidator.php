<?php

declare(strict_types=1);

namespace GardenManager\Permission\Application\Service;

use GardenManager\Permission\Domain\Exception\PermissionException;
use GardenManager\Permission\Domain\Service\PermissionMatcher;
use GardenManager\Permission\Domain\ValueObject\PermissionEntryParser;
use GardenManager\Permission\Domain\ValueObject\TenantPermissionConfig;

final readonly class PermissionConfigValidator
{
    public function __construct(
        private PermissionRegistryService $permissionRegistry,
        private PermissionMatcher $permissionMatcher,
    ) {
    }

    public function validate(TenantPermissionConfig $config): void
    {
        $errors = $this->collectErrors($config);

        if ($errors !== []) {
            throw PermissionException::invalidConfig($errors);
        }
    }

    /**
     * @return list<string>
     */
    public function collectErrors(TenantPermissionConfig $config): array
    {
        $errors = [];
        $knownPermissions = $this->permissionRegistry->getAll();
        $groupSlugs = array_keys($config->getGroups());

        foreach ($config->getGroups() as $slug => $group) {
            foreach ($group->parents as $parentSlug) {
                if (!$config->hasGroup($parentSlug)) {
                    $errors[] = sprintf('Group "%s" references nonexistent parent group "%s".', $slug, $parentSlug);
                }
            }

            foreach ($group->permissions as $entry) {
                try {
                    $parsed = PermissionEntryParser::parse($entry);
                    $permission = $parsed->permission;
                } catch (\InvalidArgumentException) {
                    $errors[] = sprintf('Group "%s" has permission entry "%s" without a "+" or "-" prefix.', $slug, $entry);

                    continue;
                }

                if (!$this->matchesAnyKnown($permission, $knownPermissions)) {
                    $errors[] = sprintf('Group "%s" has unrecognized permission "%s".', $slug, $permission);
                }
            }
        }

        foreach ($config->getAllUserAssignments() as $userId => $assignedSlugs) {
            foreach ($assignedSlugs as $assignedSlug) {
                if (!in_array($assignedSlug, $groupSlugs, true)) {
                    $errors[] = sprintf('User "%s" is assigned to nonexistent group "%s".', $userId, $assignedSlug);
                }
            }
        }

        foreach ($config->getAllUserOverrides() as $userId => $overrides) {
            foreach ($overrides as $entry) {
                try {
                    $parsed = PermissionEntryParser::parse($entry);
                    $permission = $parsed->permission;
                } catch (\InvalidArgumentException) {
                    $errors[] = sprintf('User "%s" has override "%s" without a "+" or "-" prefix.', $userId, $entry);

                    continue;
                }

                if (!$this->matchesAnyKnown($permission, $knownPermissions)) {
                    $errors[] = sprintf('User "%s" has unrecognized override permission "%s".', $userId, $permission);
                }
            }
        }

        $circularError = $config->detectInheritanceCycle();

        if ($circularError !== null) {
            $errors[] = $circularError;
        }

        return $errors;
    }

    /**
     * Checks if a permission string (possibly a wildcard pattern) matches
     * at least one known permission. Exact permissions are checked with in_array
     * for speed; patterns containing '*' use PermissionMatcher.
     *
     * @param list<string> $knownPermissions
     */
    private function matchesAnyKnown(string $permission, array $knownPermissions): bool
    {
        if ($permission === '**' || in_array($permission, $knownPermissions, true)) {
            return true;
        }

        if (!str_contains($permission, '*')) {
            return false;
        }
        return array_any(
            $knownPermissions, fn(string $known): bool => $this->permissionMatcher->matches($permission, $known)
        );
    }
}
