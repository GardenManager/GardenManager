<?php

declare(strict_types=1);

namespace GardenManager\Shared\Infrastructure\Doctrine\Filter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;
use GardenManager\Shared\Domain\SoftDeletable;

final class SoftDeleteFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, string $targetTableAlias): string
    {
        if (!$targetEntity->getReflectionClass()->implementsInterface(SoftDeletable::class)) {
            return '';
        }

        return $targetTableAlias . '.deleted_at IS NULL';
    }
}
