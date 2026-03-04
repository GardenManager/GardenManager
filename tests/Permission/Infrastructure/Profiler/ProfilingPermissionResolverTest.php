<?php

declare(strict_types=1);

namespace GardenManager\Tests\Permission\Infrastructure\Profiler;

use GardenManager\Permission\Domain\Service\PermissionMatcher;
use GardenManager\Permission\Domain\Service\PermissionResolverInterface;
use GardenManager\Permission\Infrastructure\Profiler\PermissionProfilerDataStore;
use GardenManager\Permission\Infrastructure\Profiler\ProfilingPermissionResolver;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
final class ProfilingPermissionResolverTest extends TestCase
{
    private Ulid $userId;
    private Ulid $tenantId;

    protected function setUp(): void
    {
        $this->userId = new Ulid();
        $this->tenantId = new Ulid();
    }

    #[Test]
    public function hasPermissionDelegatesAndRecordsCheck(): void
    {
        $inner = $this->createStub(PermissionResolverInterface::class);
        $inner->method('resolvePermissions')->willReturn(['plant.view' => true, 'plant.edit' => false]);

        $dataStore = new PermissionProfilerDataStore();
        $resolver = new ProfilingPermissionResolver($inner, $dataStore, new PermissionMatcher());

        $result = $resolver->hasPermission($this->userId, $this->tenantId, 'plant.view');

        self::assertTrue($result);

        $checks = $dataStore->getChecks();
        self::assertCount(1, $checks);
        self::assertSame('plant.view', $checks[0]['permission']);
        self::assertSame($this->tenantId->toString() . ':' . $this->userId->toString(), $checks[0]['resolveKey']);
        self::assertTrue($checks[0]['result']);
        self::assertArrayHasKey('callerFile', $checks[0]);
        self::assertArrayHasKey('callerLine', $checks[0]);
    }

    #[Test]
    public function hasPermissionRecordsDenied(): void
    {
        $inner = $this->createStub(PermissionResolverInterface::class);
        $inner->method('resolvePermissions')->willReturn(['plant.view' => true]);

        $dataStore = new PermissionProfilerDataStore();
        $resolver = new ProfilingPermissionResolver($inner, $dataStore, new PermissionMatcher());

        $result = $resolver->hasPermission($this->userId, $this->tenantId, 'plant.edit');

        self::assertFalse($result);

        $checks = $dataStore->getChecks();
        self::assertCount(1, $checks);
        self::assertFalse($checks[0]['result']);
    }

    #[Test]
    public function resolvePermissionsDelegatesToInner(): void
    {
        $permissions = ['plant.view' => true, 'seller.list' => true];

        $inner = $this->createMock(PermissionResolverInterface::class);
        $inner->expects(self::once())
            ->method('resolvePermissions')
            ->with($this->userId, $this->tenantId)
            ->willReturn($permissions);

        $dataStore = new PermissionProfilerDataStore();
        $resolver = new ProfilingPermissionResolver($inner, $dataStore, new PermissionMatcher());

        $result = $resolver->resolvePermissions($this->userId, $this->tenantId);

        self::assertSame($permissions, $result);
    }

    #[Test]
    public function respectsCurrentSourceFromDataStore(): void
    {
        $inner = $this->createStub(PermissionResolverInterface::class);
        $inner->method('resolvePermissions')->willReturn(['plant.view' => true]);

        $dataStore = new PermissionProfilerDataStore();
        $dataStore->setCurrentSource('twig');

        $resolver = new ProfilingPermissionResolver($inner, $dataStore, new PermissionMatcher());
        $resolver->hasPermission($this->userId, $this->tenantId, 'plant.view');

        $checks = $dataStore->getChecks();
        self::assertSame('twig', $checks[0]['source']);
    }
}
