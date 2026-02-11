<?php

declare(strict_types=1);

namespace GardenManager\Auth\Application\Dto;

final readonly class PendingOidcLink
{
    public const string SESSION_KEY = '_oidc_pending_link';

    public function __construct(
        public string $email,
        public string $provider,
        public string $subject,
    ) {
    }
}
