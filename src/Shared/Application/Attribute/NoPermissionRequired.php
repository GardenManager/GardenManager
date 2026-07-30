<?php

declare(strict_types=1);

namespace GardenManager\Shared\Application\Attribute;

use Attribute;
use InvalidArgumentException;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class NoPermissionRequired
{
    public function __construct(
        public string $reason,
    ) {
        if (trim($reason) === '') {
            throw new InvalidArgumentException(
                'A non-empty reason is required to exempt a message from permission checks.',
            );
        }
    }
}
