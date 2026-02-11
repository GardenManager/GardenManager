<?php

namespace GardenManager\Shared\Domain\Exception\Contract;

interface HttpStatusCodeCarrierExceptionInterface
{
    public function getHttpStatusCode(): int;
}
