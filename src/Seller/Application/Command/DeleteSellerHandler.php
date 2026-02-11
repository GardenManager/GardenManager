<?php

namespace GardenManager\Seller\Application\Command;

use GardenManager\Seller\Domain\SellerRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class DeleteSellerHandler
{
    public function __construct(
        private SellerRepositoryInterface $sellerRepository,
    ) {
    }

    public function __invoke(DeleteSellerCommand $command): void
    {
        $seller = $this->sellerRepository->getByIdForOwner($command->sellerId, $command->ownerId);

        $seller->softDelete();
        $this->sellerRepository->save($seller);
    }
}
