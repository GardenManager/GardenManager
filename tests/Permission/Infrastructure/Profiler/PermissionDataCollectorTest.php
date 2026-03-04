<?php

declare(strict_types=1);

namespace GardenManager\Tests\Permission\Infrastructure\Profiler;

use GardenManager\Permission\Infrastructure\Profiler\PermissionDataCollector;
use GardenManager\Permission\Infrastructure\Profiler\PermissionProfilerDataStore;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Group('unit')]
final class PermissionDataCollectorTest extends TestCase
{
    #[Test]
    public function getNameReturnsCollectorId(): void
    {
        $collector = new PermissionDataCollector();
        self::assertSame('app.permission', $collector->getName());
    }

    #[Test]
    public function collectSerializesDataFromStore(): void
    {
        $store = new PermissionProfilerDataStore();
        $store->recordCacheStatus('l1');
        $store->recordCheck('plant.view', 'tenant-1:user-1', true);
        $store->recordCacheStatus('miss');
        $store->recordCheck('plant.edit', 'tenant-1:user-1', false);
        $store->recordResolve('tenant-1:user-1', ['is_owner' => false, 'assigned_groups' => ['viewer']]);

        $collector = new PermissionDataCollector($store);
        $collector->collect(new Request(), new Response());

        self::assertSame(2, $collector->getTotalChecks());
        self::assertSame(1, $collector->getDeniedCount());
        self::assertSame(1, $collector->getL1Hits());
        self::assertSame(0, $collector->getL2Hits());
        self::assertSame(1, $collector->getCacheMisses());
    }

    #[Test]
    public function collectWithoutStoreProducesEmptyData(): void
    {
        $collector = new PermissionDataCollector();
        $collector->collect(new Request(), new Response());

        self::assertSame(0, $collector->getTotalChecks());
        self::assertSame(0, $collector->getDeniedCount());
        self::assertSame([], $collector->getChecks());
        self::assertSame([], $collector->getResolves());
    }

    #[Test]
    public function cacheHitRatioCalculation(): void
    {
        $store = new PermissionProfilerDataStore();
        $store->recordCacheStatus('l1');
        $store->recordCheck('a', 't:u', true);
        $store->recordCacheStatus('l1');
        $store->recordCheck('b', 't:u', true);
        $store->recordCacheStatus('l2');
        $store->recordCheck('c', 't:u', true);
        $store->recordCacheStatus('miss');
        $store->recordCheck('d', 't:u', true);

        $collector = new PermissionDataCollector($store);
        $collector->collect(new Request(), new Response());

        // 3 hits out of 4 = 75%
        self::assertSame(75.0, $collector->getCacheHitRatio());
    }

    #[Test]
    public function cacheHitRatioReturnsZeroWhenNoResolutions(): void
    {
        $collector = new PermissionDataCollector();
        $collector->collect(new Request(), new Response());

        self::assertSame(0.0, $collector->getCacheHitRatio());
    }

    #[Test]
    public function checksReturnFullData(): void
    {
        $store = new PermissionProfilerDataStore();
        $store->setCurrentSource('twig');
        $store->recordCacheStatus('l2');
        $store->recordCheck('plant.view', 'tenant-1:user-1', true, 'templates/plant/list.html.twig', 15);

        $collector = new PermissionDataCollector($store);
        $collector->collect(new Request(), new Response());

        $checks = $collector->getChecks();
        self::assertCount(1, $checks);
        self::assertSame('plant.view', $checks[0]['permission']);
        self::assertSame('twig', $checks[0]['source']);
        self::assertSame('l2', $checks[0]['cacheStatus']);
        self::assertTrue($checks[0]['result']);
        self::assertSame('templates/plant/list.html.twig', $checks[0]['callerFile']);
        self::assertSame(15, $checks[0]['callerLine']);
    }

    #[Test]
    public function resolvesReturnTraceData(): void
    {
        $store = new PermissionProfilerDataStore();
        $trace = [
            'is_owner' => true,
            'assigned_groups' => [],
            'hierarchy_resolved' => [],
            'groups_applied' => [],
            'user_overrides' => [],
        ];
        $store->recordResolve('t1:u1', $trace);

        $collector = new PermissionDataCollector($store);
        $collector->collect(new Request(), new Response());

        $resolves = $collector->getResolves();
        self::assertArrayHasKey('t1:u1', $resolves);
        self::assertTrue($resolves['t1:u1']['is_owner']);
    }

    #[Test]
    public function resetClearsData(): void
    {
        $store = new PermissionProfilerDataStore();
        $store->recordCheck('plant.view', 't1:u1', true);

        $collector = new PermissionDataCollector($store);
        $collector->collect(new Request(), new Response());

        self::assertSame(1, $collector->getTotalChecks());

        $collector->reset();
        self::assertSame(0, $collector->getTotalChecks());
    }
}
