<?php

declare(strict_types=1);

namespace GardenManager\Permission\Application\Service;

use GardenManager\Permission\Domain\Service\PermissionMatcher;
use GardenManager\Permission\Domain\Service\PermissionResolverInterface;
use GardenManager\Permission\Domain\ValueObject\TenantPermissionConfig;
use GardenManager\Tenant\Domain\TenantMembershipRepositoryInterface;
use GardenManager\Tenant\Domain\TenantRepositoryInterface;
use Symfony\Component\Uid\Ulid;

final class PermissionResolver implements PermissionResolverInterface
{
    public function __construct(
        private readonly TenantRepositoryInterface $tenantRepository,
        private readonly TenantMembershipRepositoryInterface $membershipRepository,
        private readonly PermissionMatcher $matcher,
    ) {
    }

    public function hasPermission(Ulid $userId, Ulid $tenantId, string $permission): bool
    {
        $resolved = $this->resolvePermissions($userId, $tenantId);

        return $this->matcher->evaluate($resolved, $permission);
    }

    /**
     * @return array<string, bool>
     */
    public function resolvePermissions(Ulid $userId, Ulid $tenantId): array
    {
        // Check tenant owner — owners have irrevocable full access
        $membership = $this->membershipRepository->findByTenantIdAndUserId($tenantId, $userId);
        if ($membership !== null && $membership->isOwner()) {
            return ['**' => true];
        }

        $config = $this->tenantRepository->getById($tenantId)->getPermissionsConfig();

        // Collect user groups
        $assignedSlugs = $config->getUserAssignments((string) $userId);

        // Resolve all groups with inheritance ordered by priority
        $allGroups = [];
        $visited = [];
        foreach ($assignedSlugs as $slug) {
            $this->resolveGroupHierarchy($slug, $config, $allGroups, $visited);
        }

        // Sort by priority (low to high — higher priority overwrites)
        usort($allGroups, static fn (array $a, array $b): int => $a['priority'] <=> $b['priority']);

        // Flatten permissions from groups
        $map = [];
        foreach ($allGroups as $groupEntry) {
            foreach ($groupEntry['permissions'] as $permission => $granted) {
                $map[$permission] = $granted;
            }
        }

        // Apply user overrides (most specific wins over groups)
        foreach ($config->getResolvedUserOverrides((string) $userId) as $permission => $granted) {
            $map[$permission] = $granted;
        }

        return $map;
    }

    /**
     * @param array<string, array{slug: string, priority: int, permissions: array<string, bool>}> $collected
     * @param array<string, true> $visited
     */
    private function resolveGroupHierarchy(
        string $slug,
        TenantPermissionConfig $config,
        array &$collected,
        array &$visited,
    ): void {
        if (isset($visited[$slug])) {
            return;
        }

        $group = $config->getGroup($slug);
        if ($group === null) {
            return;
        }

        $visited[$slug] = true;

        // Resolve parents first (lower priority will be overwritten by child)
        foreach ($group->parents as $parentSlug) {
            $this->resolveGroupHierarchy($parentSlug, $config, $collected, $visited);
        }

        $collected[$slug] = [
            'slug' => $slug,
            'priority' => $group->priority,
            'permissions' => $group->getResolvedPermissionMap(),
        ];
    }
}
