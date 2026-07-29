<?php

declare(strict_types=1);

namespace GardenManager\Seller\Application\Command;

use GardenManager\Seller\Domain\Entity\Seller;
use GardenManager\Seller\Domain\Persistence\SellerRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class CreateSellerCommandHandler
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
            tenantId: $command->tenantId,
            phone: $command->phone,
            description: $command->description,
            address: $command->address?->toAddress(),
            sellerId: $command->sellerId,
        );

        $this->sellerRepository->save($seller);
    }
}
