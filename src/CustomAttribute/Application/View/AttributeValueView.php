<?php

declare(strict_types=1);

namespace GardenManager\CustomAttribute\Application\View;

use GardenManager\CustomAttribute\Domain\Entity\CustomAttributeValue;
use GardenManager\CustomAttribute\Domain\Enum\AttributeTypeEnum;
use Symfony\Component\Uid\Ulid;

final readonly class AttributeValueView
{
    public function __construct(
        public Ulid $definitionId,
        public string $name,
        public string $label,
        public AttributeTypeEnum $type,
        public mixed $value,
        public int $sortOrder,
    ) {
    }

    public static function fromValue(CustomAttributeValue $value): self
    {
        $definition = $value->getDefinition();

        return new self(
            definitionId: $definition->getId(),
            name: $definition->getName(),
            label: $definition->getLabel(),
            type: $definition->getType(),
            value: $value->getValue(),
            sortOrder: $definition->getSortOrder(),
        );
    }
}
