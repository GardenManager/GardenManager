<?php

declare(strict_types=1);

namespace GardenManager\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class FlashMessages
{
    private const array TYPE_MAP = [
        'success' => 'success',
        'error' => 'error',
        'danger' => 'error',
        'warning' => 'warning',
        'info' => 'info',
        'notice' => 'info',
    ];

    public function getAlertType(string $flashType): string
    {
        return self::TYPE_MAP[$flashType] ?? 'info';
    }
}
