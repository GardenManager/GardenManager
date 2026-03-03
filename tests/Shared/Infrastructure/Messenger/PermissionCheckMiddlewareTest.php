<?php

declare(strict_types=1);

namespace GardenManager\Tests\Shared\Infrastructure\Messenger;

use GardenManager\Permission\Domain\Exception\PermissionException;
use GardenManager\Permission\Domain\Service\PermissionResolverInterface;
use GardenManager\Shared\Application\Attribute\RequiresPermission;
use GardenManager\Shared\Application\AuthorizedMessageInterface;
use GardenManager\Shared\Application\CommandInterface;
use GardenManager\Shared\Infrastructure\Messenger\PermissionCheckMiddleware;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
final class PermissionCheckMiddlewareTest extends TestCase
{
    #[Test]
    public function passesThroughWhenNoAttributeOnMessage(): void
    {
        $resolver = $this->createStub(PermissionResolverInterface::class);
        $middleware = new PermissionCheckMiddleware($resolver);

        $message = new class implements CommandInterface {};
        $envelope = new Envelope($message);

        $nextMiddleware = $this->createStub(MiddlewareInterface::class);
        $nextMiddleware->method('handle')->willReturn($envelope);

        $stack = $this->createStub(StackInterface::class);
        $stack->method('next')->willReturn($nextMiddleware);

        $result = $middleware->handle($envelope, $stack);
        self::assertSame($envelope, $result);
    }

    #[Test]
    public function passesThroughWhenMessageDoesNotImplementInterface(): void
    {
        $resolver = $this->createStub(PermissionResolverInterface::class);
        $middleware = new PermissionCheckMiddleware($resolver);

        $message = new #[RequiresPermission('test.permission')] class implements CommandInterface {};
        $envelope = new Envelope($message);

        $nextMiddleware = $this->createStub(MiddlewareInterface::class);
        $nextMiddleware->method('handle')->willReturn($envelope);

        $stack = $this->createStub(StackInterface::class);
        $stack->method('next')->willReturn($nextMiddleware);

        $result = $middleware->handle($envelope, $stack);
        self::assertSame($envelope, $result);
    }

    #[Test]
    public function grantsAccessWhenPermissionIsGranted(): void
    {
        $resolver = $this->createStub(PermissionResolverInterface::class);
        $resolver->method('hasPermission')->willReturn(true);

        $middleware = new PermissionCheckMiddleware($resolver);

        $message = $this->createAuthorizedMessage();
        $envelope = new Envelope($message);

        $nextMiddleware = $this->createStub(MiddlewareInterface::class);
        $nextMiddleware->method('handle')->willReturn($envelope);

        $stack = $this->createStub(StackInterface::class);
        $stack->method('next')->willReturn($nextMiddleware);

        $result = $middleware->handle($envelope, $stack);
        self::assertSame($envelope, $result);
    }

    #[Test]
    public function throwsPermissionExceptionWhenAccessDenied(): void
    {
        $resolver = $this->createStub(PermissionResolverInterface::class);
        $resolver->method('hasPermission')->willReturn(false);

        $middleware = new PermissionCheckMiddleware($resolver);

        $message = $this->createAuthorizedMessage();
        $envelope = new Envelope($message);

        $stack = $this->createStub(StackInterface::class);

        $this->expectException(PermissionException::class);

        $middleware->handle($envelope, $stack);
    }

    private function createAuthorizedMessage(): object
    {
        return new #[RequiresPermission('test.permission')] class implements CommandInterface, AuthorizedMessageInterface {
            public function getActorUserId(): Ulid
            {
                return new Ulid();
            }

            public function getTenantId(): Ulid
            {
                return new Ulid();
            }
        };
    }
}
