<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Domain;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use GardenManager\Permission\Domain\ValueObject\TenantPermissionConfig;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity]
#[ORM\Table(name: 'tenant')]
#[ORM\HasLifecycleCallbacks]
class Tenant
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column(type: 'ulid', unique: true)]
    private Ulid $id;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    #[ORM\Column]
    private DateTimeImmutable $updatedAt;

    #[ORM\Column(type: 'tenant_permission_config')]
    private TenantPermissionConfig $permissionsConfig;

    #[ORM\Version]
    #[ORM\Column(type: 'integer')]
    private int $version = 1;

    private function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
        $this->permissionsConfig = new TenantPermissionConfig();
    }

    public static function create(string $name, ?Ulid $id = null): self
    {
        $tenant = new self();

        $tenant->id = $id ?? new Ulid();
        $tenant->name = $name;

        return $tenant;
    }

    public function update(
        string $name,
    ): void {
        $this->name = $name;
    }

    public function getId(): Ulid
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getPermissionsConfig(): TenantPermissionConfig
    {
        return $this->permissionsConfig;
    }

    public function updatePermissionsConfig(TenantPermissionConfig $permissionsConfig): void
    {
        $this->permissionsConfig = $permissionsConfig;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}
