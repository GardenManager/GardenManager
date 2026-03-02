<?php

declare(strict_types=1);

namespace GardenManager\Permission\Infrastructure\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class ValidPermissionEntryValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ValidPermissionEntry) {
            throw new UnexpectedTypeException($constraint, ValidPermissionEntry::class);
        }

        if ($value === null || $value === '') {
            $this->context->buildViolation($constraint->blankMessage)
                ->addViolation();

            return;
        }

        if (!$constraint->prefixed) {
            return;
        }

        if (strlen((string) $value) < 4) {
            $this->context->buildViolation($constraint->invalidPermissionMessage)
                ->addViolation();

            return;
        }

        $prefix = $value[0];

        if ($prefix !== '+' && $prefix !== '-') {
            $this->context->buildViolation($constraint->prefixMessage)
                ->addViolation();
        }
    }
}
