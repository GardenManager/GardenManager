<?php

declare(strict_types=1);

namespace GardenManager\CustomAttribute\Application\Query;

use GardenManager\CustomAttribute\Application\View\DefinitionDetailView;
use GardenManager\CustomAttribute\Domain\Persistence\CustomAttributeDefinitionRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class GetDefinitionQueryHandler
{
    public function __construct(
        private CustomAttributeDefinitionRepositoryInterface $definitionRepository,
    ) {
    }

    public function __invoke(GetDefinitionQuery $query): DefinitionDetailView
    {
        $definition = $this->definitionRepository->getById($query->definitionId);

        return DefinitionDetailView::fromEntity($definition);
    }
}
