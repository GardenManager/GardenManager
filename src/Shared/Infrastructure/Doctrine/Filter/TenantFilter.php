<?php

declare(strict_types=1);

namespace GardenManager\Shared\Infrastructure\Doctrine\Filter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;
use GardenManager\Shared\Domain\TenantScoped;

final class TenantFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, string $targetTableAlias): string
    {
        if (!$targetEntity->getReflectionClass()->implementsInterface(TenantScoped::class)) {
            return '';
        }

        return $targetTableAlias . '.tenant_id = ' . $this->getParameter('tenantId');
    }
}
