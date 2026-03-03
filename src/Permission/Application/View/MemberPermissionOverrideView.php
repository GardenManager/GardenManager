<?php

declare(strict_types=1);

namespace GardenManager\Permission\Application\View;

use GardenManager\Permission\Domain\ValueObject\PermissionEntryParser;

final readonly class MemberPermissionOverrideView
{
    public function __construct(
        public string $userId,
        public string $permission,
        public bool $granted,
    ) {
    }

    public static function fromData(string $userId, string $prefixedPermission): self
    {
        $parsed = PermissionEntryParser::parse($prefixedPermission);

        return new self(
            userId: $userId,
            permission: $parsed->permission,
            granted: $parsed->granted,
        );
    }
}
