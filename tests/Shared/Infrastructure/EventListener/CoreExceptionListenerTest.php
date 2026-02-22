<?php

declare(strict_types=1);

namespace GardenManager\Tests\Shared\Infrastructure\EventListener;

use GardenManager\Shared\Domain\Exception\CoreException;
use GardenManager\Shared\Infrastructure\EventListener\CoreExceptionListener;
use GardenManager\Shared\Infrastructure\Http\TurboStreamToastRenderer;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\UX\Turbo\TurboBundle;
use Throwable;
use Twig\Environment;

#[Group('unit')]
final class CoreExceptionListenerTest extends TestCase
{
    private CoreExceptionListener $listener;
    private CoreExceptionListener $prodListener;

    protected function setUp(): void
    {
        $twigStub = $this->createStub(Environment::class);
        $twigStub->method('render')->willReturn('<div>toast</div>');

        $toastRenderer = new TurboStreamToastRenderer($twigStub);

        $this->listener = new CoreExceptionListener($toastRenderer, 'dev');
        $this->prodListener = new CoreExceptionListener($toastRenderer, 'prod');
    }

    #[Test]
    public function ignoresNonCoreException(): void
    {
        $event = $this->createEvent(
            new RuntimeException('something broke'),
            $this->createHtmlRequest(),
        );

        $this->listener->__invoke($event);

        self::assertNull($event->getResponse());
        self::assertInstanceOf(RuntimeException::class, $event->getThrowable());
    }

    #[Test]
    public function userFacingTurboRequestReturnsTurboStreamToast(): void
    {
        $exception = $this->createUserFacingException('Oops, user error.', 422);
        $request = $this->createTurboRequest();

        $event = $this->createEvent($exception, $request);
        $this->listener->__invoke($event);

        $response = $event->getResponse();
        self::assertNotNull($response);
        self::assertSame(422, $response->getStatusCode());
        self::assertStringContainsString(TurboBundle::STREAM_MEDIA_TYPE, $response->headers->get('Content-Type', ''));
    }

    #[Test]
    public function userFacingNonTurboRequestFlashesAndRedirectsToReferer(): void
    {
        $exception = $this->createUserFacingException('Bad input.', 400);
        $session = new Session(new MockArraySessionStorage());
        $request = $this->createHtmlRequest('/previous-page');
        $request->setSession($session);

        $event = $this->createEvent($exception, $request);
        $this->listener->__invoke($event);

        $response = $event->getResponse();
        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/previous-page', $response->getTargetUrl());
        self::assertSame(['Bad input.'], $session->getFlashBag()->get('error'));
    }

    #[Test]
    public function noRefererRedirectsToRoot(): void
    {
        $exception = $this->createUserFacingException('Bad input.', 400);
        $session = new Session(new MockArraySessionStorage());
        $request = $this->createHtmlRequest();
        $request->setSession($session);

        $event = $this->createEvent($exception, $request);
        $this->listener->__invoke($event);

        $response = $event->getResponse();
        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/', $response->getTargetUrl());
    }

    #[Test]
    public function nonUserFacingNonTurboInDevWrapsInHttpExceptionAndPassesThrough(): void
    {
        $exception = $this->createNonUserFacingException(403);
        $request = $this->createHtmlRequest();

        $event = $this->createEvent($exception, $request);
        $this->listener->__invoke($event);

        self::assertNull($event->getResponse());

        $throwable = $event->getThrowable();
        self::assertInstanceOf(HttpException::class, $throwable);
        self::assertSame(403, $throwable->getStatusCode());
        self::assertSame($exception, $throwable->getPrevious());
    }

    #[Test]
    public function nonUserFacingNonTurboInTestWrapsInHttpExceptionAndPassesThrough(): void
    {
        $twigStub = $this->createStub(Environment::class);
        $twigStub->method('render')->willReturn('<div>toast</div>');
        $testListener = new CoreExceptionListener(new TurboStreamToastRenderer($twigStub), 'test');

        $exception = $this->createNonUserFacingException(404);
        $request = $this->createHtmlRequest();

        $event = $this->createEvent($exception, $request);
        $testListener->__invoke($event);

        self::assertNull($event->getResponse());

        $throwable = $event->getThrowable();
        self::assertInstanceOf(HttpException::class, $throwable);
        self::assertSame(404, $throwable->getStatusCode());
        self::assertSame($exception, $throwable->getPrevious());
    }

    #[Test]
    public function nonUserFacingTurboInDevShowsExceptionMessageAsToast(): void
    {
        $exception = $this->createNonUserFacingException();
        $request = $this->createTurboRequest();

        $event = $this->createEvent($exception, $request);
        $this->listener->__invoke($event);

        $response = $event->getResponse();
        self::assertNotNull($response);
        self::assertSame(422, $response->getStatusCode());
        self::assertStringContainsString(TurboBundle::STREAM_MEDIA_TYPE, $response->headers->get('Content-Type', ''));
    }

    #[Test]
    public function nonUserFacingInProdShowsGenericMessage(): void
    {
        $exception = $this->createNonUserFacingException();
        $session = new Session(new MockArraySessionStorage());
        $request = $this->createHtmlRequest('/some-page');
        $request->setSession($session);

        $event = $this->createEvent($exception, $request);
        $this->prodListener->__invoke($event);

        $response = $event->getResponse();
        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame(['Something went wrong. Please try again.'], $session->getFlashBag()->get('error'));
    }

    #[Test]
    public function nonUserFacingInProdTurboShowsGenericToast(): void
    {
        $exception = $this->createNonUserFacingException();
        $request = $this->createTurboRequest();

        $event = $this->createEvent($exception, $request);
        $this->prodListener->__invoke($event);

        $response = $event->getResponse();
        self::assertNotNull($response);
        self::assertSame(422, $response->getStatusCode());
    }

    #[Test]
    public function jsonRequestWrapsInHttpExceptionAndPassesThrough(): void
    {
        $exception = $this->createUserFacingException('Oops.', 422);
        $request = Request::create('/api/test', 'POST');
        $request->headers->set('Accept', 'application/json');

        $event = $this->createEvent($exception, $request);
        $this->listener->__invoke($event);

        self::assertNull($event->getResponse());

        $throwable = $event->getThrowable();
        self::assertInstanceOf(HttpException::class, $throwable);
        self::assertSame(422, $throwable->getStatusCode());
        self::assertSame($exception, $throwable->getPrevious());
    }

    private function createEvent(Throwable $exception, Request $request): ExceptionEvent
    {
        return new ExceptionEvent(
            $this->createStub(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $exception,
        );
    }

    private function createHtmlRequest(?string $referer = null): Request
    {
        $request = Request::create('/test', 'POST');
        $request->headers->set('Accept', 'text/html');

        if ($referer !== null) {
            $request->headers->set('Referer', $referer);
        }

        return $request;
    }

    private function createTurboRequest(): Request
    {
        $request = Request::create('/test', 'POST');
        $request->headers->set('Accept', TurboBundle::STREAM_MEDIA_TYPE . ', text/html');

        return $request;
    }

    private function createUserFacingException(string $message, int $httpStatus): CoreException
    {
        return new class($message, $httpStatus) extends CoreException {
            public function __construct(string $message, int $httpStatus)
            {
                parent::__construct($message, [], $httpStatus, userFacingMessage: $message);
            }
        };
    }

    private function createNonUserFacingException(int $httpStatus = 500): CoreException
    {
        return new class($httpStatus) extends CoreException {
            public function __construct(int $httpStatus)
            {
                parent::__construct('Internal error', [], $httpStatus);
            }
        };
    }
}
