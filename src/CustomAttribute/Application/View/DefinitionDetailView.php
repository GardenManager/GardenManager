<?php

declare(strict_types=1);

namespace GardenManager\CustomAttribute\Application\View;

use DateTimeImmutable;
use GardenManager\CustomAttribute\Domain\Entity\CustomAttributeDefinition;
use GardenManager\CustomAttribute\Domain\Enum\AttributeTypeEnum;
use Symfony\Component\Uid\Ulid;

final readonly class DefinitionDetailView
{
    /**
     * @param list<string>|null $options
     */
    public function __construct(
        public Ulid $id,
        public string $entityType,
        public string $name,
        public string $label,
        public AttributeTypeEnum $type,
        public bool $required,
        public int $sortOrder,
        public ?array $options,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt,
    ) {
    }

    public static function fromEntity(CustomAttributeDefinition $definition): self
    {
        return new self(
            id: $definition->getId(),
            entityType: $definition->getEntityType(),
            name: $definition->getName(),
            label: $definition->getLabel(),
            type: $definition->getType(),
            required: $definition->isRequired(),
            sortOrder: $definition->getSortOrder(),
            options: $definition->getOptions(),
            createdAt: $definition->getCreatedAt(),
            updatedAt: $definition->getUpdatedAt(),
        );
    }
}
