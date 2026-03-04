<?php

declare(strict_types=1);

namespace GardenManager\Tests\Permission\Infrastructure\Profiler;

use GardenManager\Permission\Infrastructure\Profiler\PermissionProfilerDataStore;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class PermissionProfilerDataStoreTest extends TestCase
{
    #[Test]
    public function recordsChecksWithCurrentSource(): void
    {
        $store = new PermissionProfilerDataStore();

        $store->recordCacheStatus('l1');
        $store->recordCheck('plant.view', 'tenant-1:user-1', true);

        $checks = $store->getChecks();
        self::assertCount(1, $checks);
        self::assertSame('plant.view', $checks[0]['permission']);
        self::assertSame('tenant-1:user-1', $checks[0]['resolveKey']);
        self::assertTrue($checks[0]['result']);
        self::assertSame('direct', $checks[0]['source']);
        self::assertSame('l1', $checks[0]['cacheStatus']);
        self::assertNull($checks[0]['callerFile']);
        self::assertNull($checks[0]['callerLine']);
    }

    #[Test]
    public function recordsChecksWithCallerLocation(): void
    {
        $store = new PermissionProfilerDataStore();

        $store->recordCheck('plant.view', 'tenant-1:user-1', true, 'templates/plant/list.html.twig', 42);

        $checks = $store->getChecks();
        self::assertCount(1, $checks);
        self::assertSame('templates/plant/list.html.twig', $checks[0]['callerFile']);
        self::assertSame(42, $checks[0]['callerLine']);
    }

    #[Test]
    public function sourceTrackingWorksCorrectly(): void
    {
        $store = new PermissionProfilerDataStore();

        $store->setCurrentSource('twig');
        $store->recordCheck('plant.view', 't1:u1', true);

        $store->setCurrentSource('middleware');
        $store->recordCheck('plant.edit', 't1:u1', false);

        $store->setCurrentSource(null);
        $store->recordCheck('plant.delete', 't1:u1', true);

        $checks = $store->getChecks();
        self::assertSame('twig', $checks[0]['source']);
        self::assertSame('middleware', $checks[1]['source']);
        self::assertSame('direct', $checks[2]['source']);
    }

    #[Test]
    public function recordsResolveTraces(): void
    {
        $store = new PermissionProfilerDataStore();

        $trace = [
            'is_owner' => false,
            'assigned_groups' => ['viewer'],
            'hierarchy_resolved' => ['viewer'],
            'groups_applied' => [
                ['slug' => 'viewer', 'priority' => 0, 'permissions' => ['plant.view' => true]],
            ],
            'user_overrides' => [],
        ];

        $store->recordResolve('tenant-1:user-1', $trace);

        $resolves = $store->getResolves();
        self::assertArrayHasKey('tenant-1:user-1', $resolves);
        self::assertFalse($resolves['tenant-1:user-1']['is_owner']);
        self::assertSame(['viewer'], $resolves['tenant-1:user-1']['assigned_groups']);
    }

    #[Test]
    public function tracksCacheCounters(): void
    {
        $store = new PermissionProfilerDataStore();

        $store->recordCacheStatus('l1');
        $store->recordCheck('a', 't:u', true);
        $store->recordCacheStatus('l1');
        $store->recordCheck('b', 't:u', true);
        $store->recordCacheStatus('l2');
        $store->recordCheck('c', 't:u', true);
        $store->recordCacheStatus('miss');
        $store->recordCheck('d', 't:u', false);

        self::assertSame(2, $store->getL1Hits());
        self::assertSame(1, $store->getL2Hits());
        self::assertSame(1, $store->getCacheMisses());
    }

    #[Test]
    public function cacheStatusResetsAfterRecordingCheck(): void
    {
        $store = new PermissionProfilerDataStore();

        $store->recordCacheStatus('l1');
        $store->recordCheck('a', 't:u', true);

        // No explicit cache status set — should default to 'miss'
        $store->recordCheck('b', 't:u', true);

        $checks = $store->getChecks();
        self::assertSame('l1', $checks[0]['cacheStatus']);
        self::assertSame('miss', $checks[1]['cacheStatus']);
    }

    #[Test]
    public function defaultSourceIsDirect(): void
    {
        $store = new PermissionProfilerDataStore();

        self::assertSame('direct', $store->getCurrentSource());
    }
}
