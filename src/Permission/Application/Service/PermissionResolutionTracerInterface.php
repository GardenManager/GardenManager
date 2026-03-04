<?php

declare(strict_types=1);

namespace GardenManager\Permission\Application\Service;

interface PermissionResolutionTracerInterface
{
    /** @param array<string, mixed> $trace */
    public function recordResolve(string $resolveKey, array $trace): void;
}
