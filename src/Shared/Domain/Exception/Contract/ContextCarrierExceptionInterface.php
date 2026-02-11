<?php

declare(strict_types=1);

namespace GardenManager\Shared\Domain\Exception\Contract;

interface ContextCarrierExceptionInterface
{
    public function getContext(): array;
}
