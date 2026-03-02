<?php

declare(strict_types=1);

namespace GardenManager\Permission\Domain\ValueObject;

final readonly class PermissionGroupData
{
    public function __construct(
        public string $name,
        public int $priority,
        public array $parents,
        public array $permissions,
    ) {
    }

    public function getResolvedPermissionMap(): array
    {
        $map = [];
        foreach ($this->permissions as $entry) {
            $parsed = PermissionEntryParser::parse($entry);
            $map[$parsed['permission']] = $parsed['granted'];
        }

        return $map;
    }

    /**
     * @return array{name: string, priority: int, parents: list<string>, permissions: list<string>}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'priority' => $this->priority,
            'parents' => $this->parents,
            'permissions' => $this->permissions,
        ];
    }

    public static function fromArray(array $data): self
    {
        if (!isset($data['name']) || !\is_string($data['name'])) {
            throw new \InvalidArgumentException('PermissionGroupData requires a string "name" key.');
        }

        if (!isset($data['priority']) || !\is_int($data['priority'])) {
            throw new \InvalidArgumentException('PermissionGroupData requires an integer "priority" key.');
        }

        return new self(
            name: $data['name'],
            priority: $data['priority'],
            parents: $data['parents'] ?? [],
            permissions: $data['permissions'] ?? [],
        );
    }
}
