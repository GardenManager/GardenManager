<?php

declare(strict_types=1);

namespace GardenManager\Shared\Infrastructure\Messenger\Exception;

use GardenManager\Shared\Domain\Exception\CoreException;

final class MissingPermissionDeclarationException extends CoreException
{
    public const int CODE_MISSING_DECLARATION = 0x7D3F91A2;
    public const int CODE_CONFLICTING_DECLARATION = 0x5C8E24B7;
    public const int CODE_MISSING_AUTHORIZATION_CONTEXT = 0x3A9D60F4;

    public static function missingDeclaration(string $messageClass): self
    {
        return new self(
            message: \sprintf(
                'Message "%s" declares no authorization policy. Add #[RequiresPermission] and implement '
                . 'AuthorizedMessageInterface, or add #[NoPermissionRequired(reason: ...)] if it is genuinely exempt.',
                $messageClass,
            ),
            context: [
                'messageClass' => $messageClass,
            ],
            httpStatusCode: 500,
            code: self::CODE_MISSING_DECLARATION,
        );
    }

    public static function conflictingDeclaration(string $messageClass): self
    {
        return new self(
            message: \sprintf(
                'Message "%s" declares both #[RequiresPermission] and #[NoPermissionRequired]. Remove one of them.',
                $messageClass,
            ),
            context: [
                'messageClass' => $messageClass,
            ],
            httpStatusCode: 500,
            code: self::CODE_CONFLICTING_DECLARATION,
        );
    }

    public static function missingAuthorizationContext(string $messageClass): self
    {
        return new self(
            message: \sprintf(
                'Message "%s" has #[RequiresPermission] but does not implement AuthorizedMessageInterface, so the'
                . ' actor and tenant cannot be resolved.',
                $messageClass,
            ),
            context: [
                'messageClass' => $messageClass,
            ],
            httpStatusCode: 500,
            code: self::CODE_MISSING_AUTHORIZATION_CONTEXT,
        );
    }
}
