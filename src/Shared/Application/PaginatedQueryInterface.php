<?php

declare(strict_types=1);

namespace GardenManager\Shared\Application;

/**
 * @template TResult
 *
 * @extends QueryInterface<TResult>
 */
interface PaginatedQueryInterface extends QueryInterface
{
    public function getPage(): int;

    public function getLimit(): int;
}
