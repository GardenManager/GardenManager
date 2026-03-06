<?php

declare(strict_types=1);

namespace GardenManager\CustomAttribute\Application\Command;

use GardenManager\CustomAttribute\Domain\Persistence\CustomAttributeDefinitionRepositoryInterface;
use GardenManager\Shared\Domain\Security\TenantAccessChecker;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class DeleteDefinitionHandler
{
    public function __construct(private CustomAttributeDefinitionRepositoryInterface $definitionRepository)
    {
    }

    public function __invoke(DeleteDefinitionCommand $command): void
    {
        $definition = $this->definitionRepository->getById($command->definitionId);

        $this->definitionRepository->remove($definition);
    }
}
