<?php

declare(strict_types=1);

namespace GardenManager\Tests\Auth\Application\Command;

use GardenManager\Auth\Application\Command\VerifyEmailCommand;
use GardenManager\Auth\Application\Command\VerifyEmailHandler;
use GardenManager\Auth\Domain\AuthUser;
use GardenManager\Auth\Domain\AuthUserRepositoryInterface;
use GardenManager\Auth\Domain\Exception\AuthException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
final class VerifyEmailHandlerTest extends TestCase
{
    #[Test]
    public function marksUserAsVerified(): void
    {
        $userId = new Ulid();
        $user = AuthUser::createWithPassword($userId, 'test@example.com', 'Test', 'pw');

        self::assertFalse($user->isVerified());

        $repo = $this->createMock(AuthUserRepositoryInterface::class);
        $repo->method('findById')->with($userId)->willReturn($user);
        $repo->expects(self::once())->method('save')->with($user);

        $handler = new VerifyEmailHandler($repo);
        $handler(new VerifyEmailCommand($userId));

        self::assertTrue($user->isVerified());
    }

    #[Test]
    public function throwsWhenUserNotFound(): void
    {
        $repo = $this->createStub(AuthUserRepositoryInterface::class);
        $repo->method('findById')->willReturn(null);

        $handler = new VerifyEmailHandler($repo);

        $this->expectException(AuthException::class);

        $handler(new VerifyEmailCommand(new Ulid()));
    }
}
