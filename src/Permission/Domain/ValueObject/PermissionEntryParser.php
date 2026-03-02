<?php

declare(strict_types=1);

namespace GardenManager\Permission\Domain\ValueObject;

final class PermissionEntryParser
{
    public static function parse(string $entry): PermissionEntry
    {
        $prefix = $entry[0] ?? '';
        $permissionRaw = substr($entry, 1);

        return match ($prefix) {
            '+' => new PermissionEntry($permissionRaw, true),
            '-' => new PermissionEntry($permissionRaw, false),
            default => throw new \InvalidArgumentException(\sprintf('Permission entry "%s" must start with "+" or "-".', $entry)),
        };
    }

    public static function format(string $permission, bool $granted): string
    {
        return ($granted ? '+' : '-') . $permission;
    }
}
