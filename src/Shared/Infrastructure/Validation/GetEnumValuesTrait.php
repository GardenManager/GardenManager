<?php

declare(strict_types=1);

namespace GardenManager\Shared\Infrastructure\Validation;

trait GetEnumValuesTrait
{
    public static function getValidValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
