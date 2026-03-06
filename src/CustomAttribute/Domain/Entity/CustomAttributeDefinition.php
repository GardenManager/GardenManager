<?php

declare(strict_types=1);

namespace GardenManager\CustomAttribute\Domain\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use GardenManager\CustomAttribute\Domain\Enum\AttributeTypeEnum;
use GardenManager\Shared\Domain\TenantScoped;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity]
#[ORM\Table(name: 'custom_attribute_definition')]
#[ORM\UniqueConstraint(name: 'uq_definition_tenant_entity_name', columns: ['tenant_id', 'entity_type', 'name'])]
#[ORM\Index(name: 'idx_definition_tenant_entity', columns: ['tenant_id', 'entity_type'])]
#[ORM\HasLifecycleCallbacks]
class CustomAttributeDefinition implements TenantScoped
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column(type: 'ulid', unique: true)]
    private Ulid $id;

    #[ORM\Column(type: 'ulid')]
    private Ulid $tenantId;

    #[ORM\Column(type: 'string', length: 64)]
    private string $entityType;

    #[ORM\Column(type: 'string', length: 128)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255)]
    private string $label;

    #[ORM\Column(type: 'string', length: 16, enumType: AttributeTypeEnum::class)]
    private AttributeTypeEnum $type;

    /** @var list<string>|null */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $options = null;

    #[ORM\Column(type: 'boolean')]
    private bool $required;

    #[ORM\Column(type: 'integer')]
    private int $sortOrder;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $updatedAt;

    private function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * @param list<string>|null $options
     */
    public static function create(
        Ulid $tenantId,
        string $entityType,
        string $name,
        string $label,
        AttributeTypeEnum $type,
        bool $required = false,
        int $sortOrder = 0,
        ?array $options = null,
        ?Ulid $definitionId = null,
    ): self {
        $definition = new self();

        $definition->id = $definitionId ?? new Ulid();
        $definition->tenantId = $tenantId;
        $definition->entityType = $entityType;
        $definition->name = $name;
        $definition->label = $label;
        $definition->type = $type;
        $definition->required = $required;
        $definition->sortOrder = $sortOrder;
        $definition->options = $options;

        return $definition;
    }

    /**
     * @param list<string>|null $options
     */
    public function update(
        string $label,
        bool $required,
        int $sortOrder,
        ?array $options = null,
    ): void {
        $this->label = $label;
        $this->required = $required;
        $this->sortOrder = $sortOrder;
        $this->options = $options;
    }

    public function getId(): Ulid
    {
        return $this->id;
    }

    public function getTenantId(): Ulid
    {
        return $this->tenantId;
    }

    public function getEntityType(): string
    {
        return $this->entityType;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getType(): AttributeTypeEnum
    {
        return $this->type;
    }

    /** @return list<string>|null */
    public function getOptions(): ?array
    {
        return $this->options;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
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
}
