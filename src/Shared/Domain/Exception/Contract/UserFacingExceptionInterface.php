<?php

declare(strict_types=1);

namespace GardenManager\Shared\Domain\Exception\Contract;

interface UserFacingExceptionInterface
{
    public function getUserFacingMessage(): ?string;
}
