<?php

declare(strict_types=1);

namespace GardenManager\Seller\Application\Command;

use GardenManager\Seller\Domain\Persistence\SellerRepositoryInterface;
use GardenManager\Shared\Domain\Security\TenantAccessChecker;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class DeleteSellerCommandHandler
{
    public function __construct(
        private SellerRepositoryInterface $sellerRepository,
        private TenantAccessChecker $tenantAccessChecker,
    ) {
    }

    public function __invoke(DeleteSellerCommand $command): void
    {
        $seller = $this->sellerRepository->getById($command->sellerId);
        $this->tenantAccessChecker->ensureTenantAccess($seller, $command->tenantId);

        $seller->softDelete();
        $this->sellerRepository->save($seller);
    }
}
