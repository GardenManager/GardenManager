<?php

declare(strict_types=1);

namespace GardenManager\Twig\Components;

use GardenManager\Shared\Domain\Pagination\PaginatedResult;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Pagination
{
    public string $route;

    /** @var PaginatedResult<mixed> */
    public PaginatedResult $pager;

    /** @var array<string, mixed> */
    public array $routeParams = [];
    private int $window = 2;

    /**
     * Returns an array of page numbers and null (representing ellipsis)
     * for rendering a windowed pagination bar.
     *
     * Example for current=6, total=20, window=2:
     *   [1, null, 4, 5, 6, 7, 8, null, 20]
     *
     * @return list<int|null>
     */
    public function getPages(): array
    {
        $total = $this->pager->totalPages();
        $current = $this->pager->currentPage;

        if ($total <= ($this->window * 2 + 5)) {
            return range(1, $total);
        }

        $pages = [];

        $rangeStart = max(2, $current - $this->window);
        $rangeEnd = min($total - 1, $current + $this->window);

        // Expand range toward the edges to avoid tiny gaps (e.g. [1, ..., 3])
        if ($rangeStart <= 3) {
            $rangeStart = 2;
        }
        if ($rangeEnd >= $total - 2) {
            $rangeEnd = $total - 1;
        }

        $pages[] = 1;

        if ($rangeStart > 2) {
            $pages[] = null;
        }

        for ($i = $rangeStart; $i <= $rangeEnd; ++$i) {
            $pages[] = $i;
        }

        if ($rangeEnd < $total - 1) {
            $pages[] = null;
        }

        $pages[] = $total;

        return $pages;
    }
}
