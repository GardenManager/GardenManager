<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Application\View;

use DateTimeImmutable;
use GardenManager\Tenant\Domain\Entity\Tenant;
use Symfony\Component\Uid\Ulid;

final readonly class TenantDetailView
{
    public function __construct(
        public Ulid $id,
        public string $name,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt,
    ) {
    }

    public static function fromEntity(Tenant $tenant): self
    {
        return new self(
            id: $tenant->getId(),
            name: $tenant->getName(),
            createdAt: $tenant->getCreatedAt(),
            updatedAt: $tenant->getUpdatedAt(),
        );
    }
}
