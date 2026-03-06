<?php

declare(strict_types=1);

namespace GardenManager\CustomAttribute\Domain\Persistence;

use GardenManager\CustomAttribute\Domain\Entity\CustomAttributeValue;
use Symfony\Component\Uid\Ulid;

interface CustomAttributeValueRepositoryInterface
{
    /** @return list<CustomAttributeValue> */
    public function findByEntityTypeAndEntityId(string $entityType, Ulid $entityId): array;

    /** @return array<string, CustomAttributeValue> keyed by definition ID string */
    public function findIndexedByDefinitionForEntity(string $entityType, Ulid $entityId): array;

    public function save(CustomAttributeValue $value): void;

    public function remove(CustomAttributeValue $value): void;
}
