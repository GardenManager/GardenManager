<?php

namespace GardenManager\Auth\Application\Query;

use GardenManager\Shared\Application\QueryInterface;

/** @implements QueryInterface<?OidcLinkView> */
final readonly class FindOidcLinkQuery implements QueryInterface
{
    public function __construct(
        public string $provider,
        public string $subject,
    ) {
    }
}
