<?php

declare(strict_types=1);

namespace GardenManager\Shared\Infrastructure\Pagination;

use Doctrine\ORM\QueryBuilder;
use GardenManager\Shared\Domain\Pagination\PaginatedResult;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

final readonly class PaginationFactory
{
    /** @return PaginatedResult<object> */
    public function createPaginatedResult(QueryBuilder $queryBuilder, int $page, int $maxPerPage): PaginatedResult
    {
        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage(
            new QueryAdapter($queryBuilder),
            $page,
            $maxPerPage,
        );

        return new PaginatedResult(
            items: iterator_to_array($pager->getCurrentPageResults()),
            currentPage: $pager->getCurrentPage(),
            perPage: $pager->getMaxPerPage(),
            totalItems: $pager->getNbResults(),
        );
    }
}
