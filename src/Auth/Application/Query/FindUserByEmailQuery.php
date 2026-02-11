<?php

declare(strict_types=1);

namespace GardenManager\Auth\Application\Query;

use GardenManager\Shared\Application\QueryInterface;

/** @implements QueryInterface<?AuthUserSummaryView> */
final readonly class FindUserByEmailQuery implements QueryInterface
{
    public function __construct(
        public string $email,
    ) {
    }
}
