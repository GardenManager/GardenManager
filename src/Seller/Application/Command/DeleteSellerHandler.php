<?php

declare(strict_types=1);

namespace GardenManager\Seller\Application\Command;

use GardenManager\Seller\Domain\SellerAccessChecker;
use GardenManager\Seller\Domain\SellerRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class DeleteSellerHandler
{
    public function __construct(
        private SellerRepositoryInterface $sellerRepository,
        private SellerAccessChecker $sellerAccessChecker,
    ) {
    }

    public function __invoke(DeleteSellerCommand $command): void
    {
        $seller = $this->sellerRepository->getById($command->sellerId);
        $this->sellerAccessChecker->ensureOwnership($seller, $command->ownerId);

        $seller->softDelete();
        $this->sellerRepository->save($seller);
    }
}
