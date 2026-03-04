<?php

declare(strict_types=1);

namespace GardenManager\Tests\Permission\Infrastructure\Console;

use GardenManager\Permission\Application\Service\PermissionCacheInvalidatorInterface;
use GardenManager\Permission\Infrastructure\Console\InvalidatePermissionCacheCommand;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
final class InvalidatePermissionCacheCommandTest extends TestCase
{
    #[Test]
    public function globalInvalidationCallsInvalidateAll(): void
    {
        $invalidator = $this->createMock(PermissionCacheInvalidatorInterface::class);
        $invalidator->expects(self::once())->method('invalidateAll');

        $tester = new CommandTester(new InvalidatePermissionCacheCommand($invalidator, 'dev'));
        $tester->execute([]);

        self::assertSame(0, $tester->getStatusCode());
        self::assertStringContainsString('all tenants and users', $tester->getDisplay());
    }

    #[Test]
    public function tenantScopedInvalidationCallsInvalidateForTenant(): void
    {
        $tenantId = new Ulid();

        $invalidator = $this->createMock(PermissionCacheInvalidatorInterface::class);
        $invalidator->expects(self::once())
            ->method('invalidateForTenant')
            ->with(self::callback(fn (Ulid $id) => $id->equals($tenantId)));

        $tester = new CommandTester(new InvalidatePermissionCacheCommand($invalidator, 'dev'));
        $tester->execute(['--tenantId' => $tenantId->toString()]);

        self::assertSame(0, $tester->getStatusCode());
        self::assertStringContainsString('tenant ' . $tenantId->toString(), $tester->getDisplay());
    }

    #[Test]
    public function userScopedInvalidationCallsInvalidateForUser(): void
    {
        $tenantId = new Ulid();
        $userId = new Ulid();

        $invalidator = $this->createMock(PermissionCacheInvalidatorInterface::class);
        $invalidator->expects(self::once())
            ->method('invalidateForUser')
            ->with(
                self::callback(fn (Ulid $id) => $id->equals($userId)),
                self::callback(fn (Ulid $id) => $id->equals($tenantId)),
            );

        $tester = new CommandTester(new InvalidatePermissionCacheCommand($invalidator, 'dev'));
        $tester->execute(['--tenantId' => $tenantId->toString(), '--userId' => $userId->toString()]);

        self::assertSame(0, $tester->getStatusCode());
        self::assertStringContainsString('user ' . $userId->toString(), $tester->getDisplay());
    }

    #[Test]
    public function userWithoutTenantShowsError(): void
    {
        $invalidator = $this->createStub(PermissionCacheInvalidatorInterface::class);

        $tester = new CommandTester(new InvalidatePermissionCacheCommand($invalidator, 'dev'));
        $tester->execute(['--userId' => (new Ulid())->toString()]);

        self::assertSame(1, $tester->getStatusCode());
        self::assertStringContainsString('--userId option requires --tenantId', $tester->getDisplay());
    }

    #[Test]
    public function invalidTenantUlidShowsError(): void
    {
        $invalidator = $this->createStub(PermissionCacheInvalidatorInterface::class);

        $tester = new CommandTester(new InvalidatePermissionCacheCommand($invalidator, 'dev'));
        $tester->execute(['--tenantId' => 'not-a-ulid']);

        self::assertSame(1, $tester->getStatusCode());
        self::assertStringContainsString('Invalid tenant ULID', $tester->getDisplay());
    }

    #[Test]
    public function invalidUserUlidShowsError(): void
    {
        $invalidator = $this->createStub(PermissionCacheInvalidatorInterface::class);
        $tenantId = new Ulid();

        $tester = new CommandTester(new InvalidatePermissionCacheCommand($invalidator, 'dev'));
        $tester->execute(['--tenantId' => $tenantId->toString(), '--userId' => 'not-a-ulid']);

        self::assertSame(1, $tester->getStatusCode());
        self::assertStringContainsString('Invalid user ULID', $tester->getDisplay());
    }

    #[Test]
    public function prodConfirmationDeclinedAborts(): void
    {
        $invalidator = $this->createMock(PermissionCacheInvalidatorInterface::class);
        $invalidator->expects(self::never())->method('invalidateAll');

        $tester = new CommandTester(new InvalidatePermissionCacheCommand($invalidator, 'prod'));
        $tester->setInputs(['no']);
        $tester->execute([]);

        self::assertSame(0, $tester->getStatusCode());
        self::assertStringContainsString('Aborted', $tester->getDisplay());
    }

    #[Test]
    public function nonProdSkipsConfirmation(): void
    {
        $invalidator = $this->createMock(PermissionCacheInvalidatorInterface::class);
        $invalidator->expects(self::once())->method('invalidateAll');

        $tester = new CommandTester(new InvalidatePermissionCacheCommand($invalidator, 'dev'));
        $tester->execute([]);

        self::assertSame(0, $tester->getStatusCode());
        self::assertStringNotContainsString('Do you want to continue?', $tester->getDisplay());
    }
}
