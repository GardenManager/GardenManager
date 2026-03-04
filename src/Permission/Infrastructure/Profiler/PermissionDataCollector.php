<?php

declare(strict_types=1);

namespace GardenManager\Permission\Infrastructure\Profiler;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

#[When('dev')]
#[AutoconfigureTag('data_collector', [
    'template' => 'profiler/permission.html.twig',
    'id' => 'app.permission',
    'priority' => 260,
])]
final class PermissionDataCollector extends DataCollector
{
    public function __construct(
        private readonly ?PermissionProfilerDataStore $dataStore = null,
    ) {
    }

    public function collect(Request $request, Response $response, ?\Throwable $exception = null): void
    {
        if ($this->dataStore === null) {
            $this->data = [
                'checks' => [],
                'resolves' => [],
                'l1_hits' => 0,
                'l2_hits' => 0,
                'cache_misses' => 0,
            ];

            return;
        }

        $this->data = [
            'checks' => $this->dataStore->getChecks(),
            'resolves' => $this->dataStore->getResolves(),
            'l1_hits' => $this->dataStore->getL1Hits(),
            'l2_hits' => $this->dataStore->getL2Hits(),
            'cache_misses' => $this->dataStore->getCacheMisses(),
        ];
    }

    public function getName(): string
    {
        return 'app.permission';
    }

    public function getTotalChecks(): int
    {
        return count($this->data['checks']);
    }

    public function getDeniedCount(): int
    {
        return count(array_filter($this->data['checks'], static fn (array $check): bool => !$check['result']));
    }

    /**
     * @return list<array{permission: string, resolveKey: string, result: bool, source: string, cacheStatus: string, callerFile: ?string, callerLine: ?int}>
     */
    public function getChecks(): array
    {
        return $this->data['checks'];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getResolves(): array
    {
        return $this->data['resolves'];
    }

    public function getL1Hits(): int
    {
        return $this->data['l1_hits'];
    }

    public function getL2Hits(): int
    {
        return $this->data['l2_hits'];
    }

    public function getCacheMisses(): int
    {
        return $this->data['cache_misses'];
    }

    public function reset(): void
    {
        $this->data = [
            'checks' => [],
            'resolves' => [],
            'l1_hits' => 0,
            'l2_hits' => 0,
            'cache_misses' => 0,
        ];
    }

    public function getCacheHitRatio(): float
    {
        $total = $this->data['l1_hits'] + $this->data['l2_hits'] + $this->data['cache_misses'];

        if ($total === 0) {
            return 0.0;
        }

        return ($this->data['l1_hits'] + $this->data['l2_hits']) / $total * 100;
    }
}
