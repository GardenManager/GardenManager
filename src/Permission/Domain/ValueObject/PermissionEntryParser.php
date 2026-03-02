<?php

declare(strict_types=1);

namespace GardenManager\Permission\Domain\ValueObject;

final class PermissionEntryParser
{
    /**
     * @return array{permission: string, granted: bool}
     */
    public static function parse(string $entry): array
    {
        $prefix = $entry[0] ?? '';
        $permissionRaw = substr($entry, 1);

        return match ($prefix) {
            '+' => ['permission' => $permissionRaw, 'granted' => true],
            '-' => ['permission' => $permissionRaw, 'granted' => false],
            default => throw new \InvalidArgumentException(\sprintf('Permission entry "%s" must start with "+" or "-".', $entry)),
        };
    }

    public static function format(string $permission, bool $granted): string
    {
        return ($granted ? '+' : '-') . $permission;
    }
}
