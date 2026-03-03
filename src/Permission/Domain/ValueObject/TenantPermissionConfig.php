<?php

declare(strict_types=1);

namespace GardenManager\Permission\Domain\ValueObject;

use GardenManager\Permission\Domain\Exception\PermissionException;

final readonly class TenantPermissionConfig
{
    /**
     * @param array<string, PermissionGroupData> $groups
     * @param array<string, list<string>> $userAssignments
     * @param array<string, list<string>> $userOverrides
     */
    public function __construct(
        private array $groups = [],
        private array $userAssignments = [],
        private array $userOverrides = [],
    ) {
    }

    public static function createEmpty(): self
    {
        return new self();
    }

    public function getGroup(string $slug): ?PermissionGroupData
    {
        return $this->groups[$slug] ?? null;
    }

    public function hasGroup(string $slug): bool
    {
        return isset($this->groups[$slug]);
    }

    /**
     * @return array<string, PermissionGroupData>
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * @return array<string, string> name => slug
     */
    public function getGroupChoices(): array
    {
        $choices = [];
        foreach ($this->groups as $slug => $group) {
            $choices[$group->name] = $slug;
        }

        return $choices;
    }

    public function withGroup(string $slug, PermissionGroupData $group): self
    {
        $groups = $this->groups;
        $groups[$slug] = $group;

        return new self($groups, $this->userAssignments, $this->userOverrides);
    }

    /**
     * @return list<string>
     */
    public function getUserAssignments(string $userId): array
    {
        return $this->userAssignments[$userId] ?? [];
    }

    /**
     * @return array<string, list<string>>
     */
    public function getAllUserAssignments(): array
    {
        return $this->userAssignments;
    }

    /**
     * @param list<string> $groupSlugs
     */
    public function withUserAssignments(string $userId, array $groupSlugs): self
    {
        $assignments = $this->userAssignments;
        $assignments[$userId] = $groupSlugs;

        return new self($this->groups, $assignments, $this->userOverrides);
    }

    public function withoutUserAssignments(string $userId): self
    {
        $assignments = $this->userAssignments;
        unset($assignments[$userId]);

        return new self($this->groups, $assignments, $this->userOverrides);
    }

    /**
     * @return list<string>
     */
    public function getUserOverrides(string $userId): array
    {
        return $this->userOverrides[$userId] ?? [];
    }

    /**
     * @return array<string, list<string>>
     */
    public function getAllUserOverrides(): array
    {
        return $this->userOverrides;
    }

    /**
     * @return array<string, bool>
     */
    public function getResolvedUserOverrides(string $userId): array
    {
        $map = [];
        foreach ($this->getUserOverrides($userId) as $entry) {
            $parsed = self::parsePermissionEntry($entry);
            $map[$parsed->permission] = $parsed->granted;
        }

        return $map;
    }

    public function withUserOverride(string $userId, string $prefixedPermission): self
    {
        $parsed = self::parsePermissionEntry($prefixedPermission);
        $basePermission = $parsed->permission;

        $existing = $this->userOverrides[$userId] ?? [];

        $filtered = array_values(array_filter(
            $existing,
            static function (string $entry) use ($basePermission): bool {
                $entryParsed = self::parsePermissionEntry($entry);

                return $entryParsed->permission !== $basePermission;
            },
        ));

        $filtered[] = $prefixedPermission;

        $overrides = $this->userOverrides;
        $overrides[$userId] = $filtered;

        return new self($this->groups, $this->userAssignments, $overrides);
    }

    public function withoutUserOverride(string $userId, string $permission): self
    {
        if (!isset($this->userOverrides[$userId])) {
            return $this;
        }

        $filtered = array_values(array_filter(
            $this->userOverrides[$userId],
            static function (string $entry) use ($permission): bool {
                $parsed = self::parsePermissionEntry($entry);

                return $parsed->permission !== $permission;
            },
        ));

        $overrides = $this->userOverrides;

        if ($filtered === []) {
            unset($overrides[$userId]);
        } else {
            $overrides[$userId] = $filtered;
        }

        return new self($this->groups, $this->userAssignments, $overrides);
    }

    /**
     * @return array{groups: array<string, array{name: string, priority: int, parents: list<string>, permissions: list<string>}>, userAssignments: array<string, list<string>>, userOverrides: array<string, list<string>>}
     */
    public function toArray(): array
    {
        $groups = [];
        foreach ($this->groups as $slug => $group) {
            $groups[$slug] = $group->toArray();
        }

        return [
            'groups' => $groups,
            'userAssignments' => $this->userAssignments,
            'userOverrides' => $this->userOverrides,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        if (isset($data['groups']) && !\is_array($data['groups'])) {
            throw PermissionException::invalidConfig(['The "groups" key must be an array.']);
        }

        if (isset($data['userAssignments']) && !\is_array($data['userAssignments'])) {
            throw PermissionException::invalidConfig(['The "userAssignments" key must be an array.']);
        }

        if (isset($data['userOverrides']) && !\is_array($data['userOverrides'])) {
            throw PermissionException::invalidConfig(['The "userOverrides" key must be an array.']);
        }

        $groups = [];
        foreach ($data['groups'] ?? [] as $slug => $groupData) {
            if (!\is_array($groupData)) {
                throw PermissionException::invalidConfig([\sprintf('Group "%s" must be an array.', $slug)]);
            }

            if (!isset($groupData['name']) || !\is_string($groupData['name'])) {
                throw PermissionException::invalidConfig([\sprintf('Group "%s" is missing a valid "name".', $slug)]);
            }

            if (!isset($groupData['priority']) || !\is_int($groupData['priority'])) {
                throw PermissionException::invalidConfig([\sprintf('Group "%s" is missing a valid "priority".', $slug)]);
            }

            $groups[$slug] = PermissionGroupData::fromArray($groupData);
        }

        return new self(
            groups: $groups,
            userAssignments: $data['userAssignments'] ?? [],
            userOverrides: $data['userOverrides'] ?? [],
        );
    }

    public function detectInheritanceCycle(): ?string
    {
        $white = 0;
        $gray = 1;
        $black = 2;

        /** @var array<string, int> $color */
        $color = [];
        foreach (array_keys($this->groups) as $slug) {
            $color[$slug] = $white;
        }

        foreach (array_keys($this->groups) as $slug) {
            if ($color[$slug] === $black) {
                continue;
            }

            $error = self::dfsVisit($slug, $this->groups, $color, $gray, $black);
            if ($error !== null) {
                return $error;
            }
        }

        return null;
    }

    public static function parsePermissionEntry(string $entry): PermissionEntry
    {
        return PermissionEntryParser::parse($entry);
    }

    /**
     * @param array<string, PermissionGroupData> $groups
     * @param array<string, int> $color
     */
    private static function dfsVisit(string $slug, array $groups, array &$color, int $gray, int $black): ?string
    {
        $color[$slug] = $gray;

        foreach ($groups[$slug]->parents as $parentSlug) {
            if (!isset($color[$parentSlug])) {
                continue;
            }

            if ($color[$parentSlug] === $gray) {
                return \sprintf('Circular inheritance detected: group "%s" has a cycle through "%s".', $slug, $parentSlug);
            }

            if ($color[$parentSlug] === $black) {
                continue;
            }

            $error = self::dfsVisit($parentSlug, $groups, $color, $gray, $black);
            if ($error !== null) {
                return $error;
            }
        }

        $color[$slug] = $black;

        return null;
    }
}
