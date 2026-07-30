<?php

declare(strict_types=1);

namespace GardenManager\Auth\Application\Query;

use GardenManager\Auth\Application\View\OidcLinkView;
use GardenManager\Shared\Application\Attribute\NoPermissionRequired;
use GardenManager\Shared\Application\QueryInterface;

/** @implements QueryInterface<?OidcLinkView> */
#[NoPermissionRequired(reason: 'Pre-authentication: OIDC link lookup during the login flow.')]
final readonly class FindOidcLinkQuery implements QueryInterface
{
    public function __construct(
        public string $provider,
        public string $subject,
    ) {
    }
}
