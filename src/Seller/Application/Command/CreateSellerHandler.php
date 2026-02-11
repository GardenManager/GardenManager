<?php

namespace GardenManager\Seller\Application\Command;

use GardenManager\Seller\Domain\Seller;
use GardenManager\Seller\Domain\SellerRepositoryInterface;
use GardenManager\Shared\Domain\ValueObject\Address;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class CreateSellerHandler
{
    public function __construct(
        private SellerRepositoryInterface $sellerRepository,
    ) {
    }

    public function __invoke(CreateSellerCommand $command): void
    {
        $seller = Seller::create(
            name: $command->name,
            email: $command->email,
            ownerId: $command->ownerId,
            phone: $command->phone,
            description: $command->description,
            address: $command->address?->toAddress(),
            id: $command->sellerId,
        );

        $this->sellerRepository->save($seller);
    }
}
