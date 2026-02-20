<?php

declare(strict_types=1);

namespace GardenManager\Seller\Application\View;

use DateTimeImmutable;
use GardenManager\Seller\Domain\Seller;
use GardenManager\Shared\Application\View\AddressView;
use Symfony\Component\Uid\Ulid;

final readonly class SellerDetailView
{
    public function __construct(
        public Ulid $id,
        public string $name,
        public string $email,
        public ?string $phone,
        public ?string $description,
        public ?AddressView $address,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt,
    ) {
    }

    public static function fromEntity(Seller $seller): self
    {
        $address = $seller->getAddress();

        return new self(
            id: $seller->getId(),
            name: $seller->getName(),
            email: $seller->getEmail(),
            phone: $seller->getPhone(),
            description: $seller->getDescription(),
            address: $address !== null ? AddressView::fromAddress($address) : null,
            createdAt: $seller->getCreatedAt(),
            updatedAt: $seller->getUpdatedAt(),
        );
    }
}
