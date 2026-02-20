<?php

declare(strict_types=1);

namespace GardenManager\Plant\Domain\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use GardenManager\Plant\Domain\Enum\LifecycleEnum;
use GardenManager\Shared\Domain\SoftDeletable;
use GardenManager\Shared\Domain\TenantScoped;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity]
#[ORM\Table(name: 'plant')]
#[ORM\HasLifecycleCallbacks]
class Plant implements SoftDeletable, TenantScoped
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column(type: 'ulid', unique: true)]
    private Ulid $id;

    #[ORM\Column(type: 'ulid')]
    private Ulid $tenantId;

    #[ORM\Column(type: 'string', length: 255)]
    private string $localName;

    #[ORM\Column(type: 'boolean')]
    private bool $isHybrid;

    #[ORM\Column(type: 'string', length: 16, enumType: LifecycleEnum::class)]
    private LifecycleEnum $lifecycle;

    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    private ?string $genus = null;

    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    private ?string $epithet = null;

    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    private ?string $cultivar = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $deletedAt = null;

    private function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public static function create(
        Ulid $tenantId,
        string $localName,
        bool $isHybrid,
        LifecycleEnum $lifecycle,
        ?string $genus = null,
        ?string $epithet = null,
        ?string $cultivar = null,
        ?Ulid $plantId = null,
    ): self {
        $plant = new self();

        $plant->id = $plantId ?? new Ulid();
        $plant->tenantId = $tenantId;
        $plant->localName = $localName;
        $plant->isHybrid = $isHybrid;
        $plant->lifecycle = $lifecycle;
        $plant->genus = $genus;
        $plant->epithet = $epithet;
        $plant->cultivar = $cultivar;

        return $plant;
    }

    public function update(
        Ulid $plantId,
        Ulid $tenantId,
        string $localName,
        bool $isHybrid,
        LifecycleEnum $lifecycle,
        ?string $genus = null,
        ?string $epithet = null,
        ?string $cultivar = null,
    ): void {
        $this->id = $plantId;
        $this->tenantId = $tenantId;
        $this->localName = $localName;
        $this->isHybrid = $isHybrid;
        $this->lifecycle = $lifecycle;
        $this->genus = $genus;
        $this->epithet = $epithet;
        $this->cultivar = $cultivar;
    }

    public function getId(): Ulid
    {
        return $this->id;
    }

    public function getTenantId(): Ulid
    {
        return $this->tenantId;
    }

    public function getLocalName(): string
    {
        return $this->localName;
    }

    public function isHybrid(): bool
    {
        return $this->isHybrid;
    }

    public function getLifecycle(): LifecycleEnum
    {
        return $this->lifecycle;
    }

    public function getGenus(): ?string
    {
        return $this->genus;
    }

    public function getEpithet(): ?string
    {
        return $this->epithet;
    }

    public function getCultivar(): ?string
    {
        return $this->cultivar;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getDeletedAt(): ?DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function softDelete(): void
    {
        $this->deletedAt = new DateTimeImmutable();
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }
}
