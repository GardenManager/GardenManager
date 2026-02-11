<?php

declare(strict_types=1);

namespace GardenManager\Shared\Infrastructure\EventListener;

use GardenManager\Shared\Domain\Exception\Contract\HttpStatusCodeCarrierExceptionInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Throwable;

#[AsEventListener(event: KernelEvents::EXCEPTION)]
final class HttpStatusCodeCarrierExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $httpStatusCodeCarrierException = $this->extractException($exception);

        if ($httpStatusCodeCarrierException === null) {
            return;
        }

        $event->setThrowable(HttpException::fromStatusCode(
            $httpStatusCodeCarrierException->getHttpStatusCode(),
            $httpStatusCodeCarrierException->getMessage(),
            $httpStatusCodeCarrierException,
            [],
            $httpStatusCodeCarrierException->getCode(),
        ));
    }

    private function extractException(Throwable $exception): ?HttpStatusCodeCarrierExceptionInterface
    {
        if ($exception instanceof HttpStatusCodeCarrierExceptionInterface) {
            return $exception;
        }

        return null;
    }
}
