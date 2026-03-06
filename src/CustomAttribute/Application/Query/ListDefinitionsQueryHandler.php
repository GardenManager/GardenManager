<?php

declare(strict_types=1);

namespace GardenManager\CustomAttribute\Application\Query;

use GardenManager\CustomAttribute\Application\View\DefinitionDetailView;
use GardenManager\CustomAttribute\Domain\Persistence\CustomAttributeDefinitionRepositoryInterface;
use GardenManager\Shared\Domain\Pagination\PaginatedResult;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class ListDefinitionsQueryHandler
{
    public function __construct(
        private CustomAttributeDefinitionRepositoryInterface $definitionRepository,
    ) {
    }

    /** @return PaginatedResult<DefinitionDetailView> */
    public function __invoke(ListDefinitionsQuery $query): PaginatedResult
    {
        return $this->definitionRepository->findPaginatedByEntityType(
            $query->entityType,
            $query->getPage(),
            $query->getLimit(),
        )->map(DefinitionDetailView::fromEntity(...));
    }
}
