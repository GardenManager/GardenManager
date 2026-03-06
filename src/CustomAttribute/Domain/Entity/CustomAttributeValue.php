<?php

declare(strict_types=1);

namespace GardenManager\CustomAttribute\Domain\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use GardenManager\CustomAttribute\Domain\Enum\AttributeTypeEnum;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity]
#[ORM\Table(name: 'custom_attribute_value')]
#[ORM\UniqueConstraint(name: 'uq_value_entity_definition', columns: ['entity_id', 'definition_id'])]
#[ORM\Index(name: 'idx_value_entity', columns: ['entity_type', 'entity_id'])]
#[ORM\Index(name: 'idx_definition_id', columns: ['definition_id'])]
#[ORM\HasLifecycleCallbacks]
class CustomAttributeValue
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column(type: 'ulid', unique: true)]
    private Ulid $id;

    #[ORM\Column(type: 'string', length: 64)]
    private string $entityType;

    #[ORM\Column(type: 'ulid')]
    private Ulid $entityId;

    #[ORM\ManyToOne(targetEntity: CustomAttributeDefinition::class)]
    #[ORM\JoinColumn(name: 'definition_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private CustomAttributeDefinition $definition;

    #[ORM\Column(type: 'string', length: 1024, nullable: true)]
    private ?string $stringValue = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $integerValue = null;

    #[ORM\Column(type: 'decimal', precision: 15, scale: 4, nullable: true)]
    private ?string $decimalValue = null;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?DateTimeImmutable $dateValue = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $booleanValue = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $updatedAt;

    private function __construct()
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    public static function create(
        CustomAttributeDefinition $definition,
        string $entityType,
        Ulid $entityId,
        mixed $value,
        ?Ulid $valueId = null,
    ): self {
        $attributeValue = new self();

        $attributeValue->id = $valueId ?? new Ulid();
        $attributeValue->definition = $definition;
        $attributeValue->entityType = $entityType;
        $attributeValue->entityId = $entityId;
        $attributeValue->setTypedValue($definition->getType(), $value);

        return $attributeValue;
    }

    public function updateValue(mixed $value): void
    {
        $this->clearValues();
        $this->setTypedValue($this->definition->getType(), $value);
    }

    public function getId(): Ulid
    {
        return $this->id;
    }

    public function getEntityType(): string
    {
        return $this->entityType;
    }

    public function getEntityId(): Ulid
    {
        return $this->entityId;
    }

    public function getDefinition(): CustomAttributeDefinition
    {
        return $this->definition;
    }

    public function getValue(): mixed
    {
        return match ($this->definition->getType()) {
            AttributeTypeEnum::STRING, AttributeTypeEnum::SELECT => $this->stringValue,
            AttributeTypeEnum::INTEGER => $this->integerValue,
            AttributeTypeEnum::DECIMAL => $this->decimalValue,
            AttributeTypeEnum::DATE => $this->dateValue,
            AttributeTypeEnum::BOOLEAN => $this->booleanValue,
        };
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    private function setTypedValue(AttributeTypeEnum $type, mixed $value): void
    {
        match ($type) {
            AttributeTypeEnum::STRING, AttributeTypeEnum::SELECT => $this->stringValue = $value !== null ? (string) $value : null,
            AttributeTypeEnum::INTEGER => $this->integerValue = $value !== null ? (int) $value : null,
            AttributeTypeEnum::DECIMAL => $this->decimalValue = $value !== null ? (string) $value : null,
            AttributeTypeEnum::DATE => $this->dateValue = $value instanceof DateTimeImmutable ? $value : null,
            AttributeTypeEnum::BOOLEAN => $this->booleanValue = $value !== null ? (bool) $value : null,
        };
    }

    private function clearValues(): void
    {
        $this->stringValue = null;
        $this->integerValue = null;
        $this->decimalValue = null;
        $this->dateValue = null;
        $this->booleanValue = null;
    }
}
