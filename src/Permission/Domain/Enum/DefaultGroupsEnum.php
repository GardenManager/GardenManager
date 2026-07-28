<?php

declare(strict_types=1);

namespace GardenManager\Permission\Domain\Enum;

enum DefaultGroupsEnum: string
{
    case Viewer = 'Viewer';
    case Editor = 'Editor';
    case Admin = 'Admin';

    public function getSlug(): string
    {
        return strtolower($this->value);
    }

    public function getPriority(): int
    {
        return match ($this) {
            self::Viewer => 0,
            self::Editor => 50,
            self::Admin => 100,
        };
    }
}
