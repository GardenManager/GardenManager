<?php

declare(strict_types=1);

namespace GardenManager\Shared\Infrastructure\EventListener;

use GardenManager\Shared\Domain\Exception\Contract\HttpStatusCodeCarrierExceptionInterface;
use GardenManager\Shared\Domain\Exception\Contract\UserFacingExceptionInterface;
use GardenManager\Shared\Infrastructure\Http\TurboStreamToastRenderer;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\UX\Turbo\TurboBundle;
use Throwable;

#[AsEventListener(event: KernelEvents::EXCEPTION, priority: -1)]
final readonly class CoreExceptionListener
{
    public function __construct(
        private TurboStreamToastRenderer $toastRenderer,
        #[Autowire('%kernel.environment%')]
        private string $environment,
    ) {
    }

    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        if (!$event->isMainRequest()
            || !$exception instanceof UserFacingExceptionInterface
        ) {
            return;
        }

        if ($request->getPreferredFormat() === 'json') {
            $this->wrapWithHttpStatus($event, $exception);

            return;
        }

        $isTurbo = \in_array(TurboBundle::STREAM_MEDIA_TYPE, $request->getAcceptableContentTypes(), true);
        $message = $exception->getUserFacingMessage();

        if ($message === null) {
            if ($this->environment !== 'prod' && !$isTurbo) {
                $this->wrapWithHttpStatus($event, $exception);

                return;
            }

            $message = $this->environment !== 'prod'
                ? \sprintf(
                    '[%s] %s: %s',
                    strtoupper($this->environment),
                    array_last(explode('\\', $exception::class)),
                    $exception->getMessage(),
                )
                : 'Something went wrong. Please try again.';
        }

        if ($isTurbo) {
            $event->setResponse($this->toastRenderer->createErrorResponse($message));

            return;
        }

        $session = $request->getSession();

        if ($session instanceof FlashBagAwareSessionInterface) {
            $session->getFlashBag()->add('error', $message);
        }

        $referer = $request->headers->get('Referer') ?? '/';
        $event->setResponse(new RedirectResponse($referer));
    }

    private function wrapWithHttpStatus(ExceptionEvent $event, Throwable $exception): void
    {
        if (!$exception instanceof HttpStatusCodeCarrierExceptionInterface) {
            return;
        }

        $event->setThrowable(HttpException::fromStatusCode(
            $exception->getHttpStatusCode(),
            $exception->getMessage(),
            $exception,
        ));
    }
}
