<?php

declare(strict_types=1);

namespace GardenManager\Seller\Application\Command;

use GardenManager\Seller\Domain\SellerAccessChecker;
use GardenManager\Seller\Domain\SellerRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class UpdateSellerHandler
{
    public function __construct(
        private SellerRepositoryInterface $sellerRepository,
        private SellerAccessChecker $sellerAccessChecker,
    ) {
    }

    public function __invoke(UpdateSellerCommand $command): void
    {
        $seller = $this->sellerRepository->getById($command->sellerId);
        $this->sellerAccessChecker->ensureOwnership($seller, $command->ownerId);

        $seller->update(
            name: $command->name,
            email: $command->email,
            phone: $command->phone,
            description: $command->description,
            address: $command->address?->toAddress(),
        );

        $this->sellerRepository->save($seller);
    }
}
