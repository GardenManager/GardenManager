<?php

declare(strict_types=1);

namespace GardenManager\Permission\Infrastructure\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class PermissionExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('has_permission', [PermissionRuntime::class, 'hasPermission']),
        ];
    }
}
