<?php

declare(strict_types=1);

namespace GardenManager\Shared\Domain\Exception;

use Exception;
use GardenManager\Shared\Domain\Exception\Contract\ContextCarrierExceptionInterface;
use GardenManager\Shared\Domain\Exception\Contract\HttpStatusCodeCarrierExceptionInterface;
use GardenManager\Shared\Domain\Exception\Contract\UserFacingExceptionInterface;
use Throwable;

abstract class CoreException extends Exception implements ContextCarrierExceptionInterface, HttpStatusCodeCarrierExceptionInterface, UserFacingExceptionInterface
{
    public function __construct(
        string $message = '',
        private readonly array $context = [],
        private readonly int $httpStatusCode = 500,
        int $code = 0,
        ?Throwable $previous = null,
        private readonly ?string $userFacingMessage = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }

    public function getUserFacingMessage(): ?string
    {
        return $this->userFacingMessage;
    }
}
