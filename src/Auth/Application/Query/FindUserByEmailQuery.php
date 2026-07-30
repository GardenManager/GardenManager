<?php

declare(strict_types=1);

namespace GardenManager\Auth\Application\Query;

use GardenManager\Auth\Application\View\AuthUserSummaryView;
use GardenManager\Shared\Application\Attribute\NoPermissionRequired;
use GardenManager\Shared\Application\QueryInterface;

/** @implements QueryInterface<?AuthUserSummaryView> */
#[NoPermissionRequired(
    reason: 'Pre-authentication: user lookup during login and registration, before an actor exists.',
)]
final readonly class FindUserByEmailQuery implements QueryInterface
{
    public function __construct(
        public string $email,
    ) {
    }
}
