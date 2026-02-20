<?php

declare(strict_types=1);

namespace GardenManager\Seller\Application\Dto\Api;

use DateTimeInterface;
use GardenManager\Seller\Application\View\SellerDetailView;
use GardenManager\Shared\Application\View\AddressView;

final readonly class SellerApiResponse
{
    public function __construct(
        public string $id,
        public string $name,
        public string $email,
        public ?string $phone,
        public ?string $description,
        public ?AddressView $address,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    public static function fromView(SellerDetailView $view): self
    {
        return new self(
            id: (string) $view->id,
            name: $view->name,
            email: $view->email,
            phone: $view->phone,
            description: $view->description,
            address: $view->address,
            createdAt: $view->createdAt->format(DateTimeInterface::ATOM),
            updatedAt: $view->updatedAt->format(DateTimeInterface::ATOM),
        );
    }
}
