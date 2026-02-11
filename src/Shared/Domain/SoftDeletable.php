<?php

declare(strict_types=1);

namespace GardenManager\Shared\Domain;

use DateTimeImmutable;

interface SoftDeletable
{
    public function getDeletedAt(): ?DateTimeImmutable;

    public function softDelete(): void;

    public function isDeleted(): bool;
}
