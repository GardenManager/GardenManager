<?php

declare(strict_types=1);

namespace GardenManager\Shared\Domain\Exception\Contract;

interface HttpStatusCodeCarrierExceptionInterface
{
    public function getHttpStatusCode(): int;
}
