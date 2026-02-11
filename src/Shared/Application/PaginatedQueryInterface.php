<?php

namespace GardenManager\Shared\Application;

/**
 * @template TResult
 * @extends QueryInterface<TResult>
 */
interface PaginatedQueryInterface extends QueryInterface
{
    public function getPage(): int;

    public function getLimit(): int;
}
