<?php

namespace GardenManager\Shared\Domain;

interface SoftDeletable
{
    public function getDeletedAt(): ?\DateTimeImmutable;

    public function softDelete(): void;

    public function isDeleted(): bool;
}
