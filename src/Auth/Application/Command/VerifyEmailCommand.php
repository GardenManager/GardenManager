<?php

declare(strict_types=1);

namespace GardenManager\Auth\Application\Command;

use GardenManager\Shared\Application\CommandInterface;
use Symfony\Component\Uid\Ulid;

final readonly class VerifyEmailCommand implements CommandInterface
{
    public function __construct(
        public Ulid $userId,
    ) {
    }
}
