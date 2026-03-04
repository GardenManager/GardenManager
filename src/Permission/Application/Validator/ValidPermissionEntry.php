<?php

declare(strict_types=1);

namespace GardenManager\Permission\Application\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class ValidPermissionEntry extends Constraint
{
    public string $blankMessage = 'The permission must not be blank.';
    public string $prefixMessage = 'The permission entry must start with "+" or "-".';
    public string $invalidPermissionMessage = 'The provided permission string is invalid.';

    public function __construct(
        public bool $prefixed = true,
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct([], $groups, $payload);
    }
}
