<?php

declare(strict_types=1);

namespace GardenManager\Permission\Infrastructure\Profiler;

use GardenManager\Permission\Application\Service\PermissionResolutionTracerInterface;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When('dev')]
final class PermissionProfilerDataStore implements PermissionResolutionTracerInterface
{
    /** @var list<array{permission: string, resolveKey: string, result: bool, source: string, cacheStatus: string, callerFile: ?string, callerLine: ?int}> */
    private array $checks = [];

    /** @var array<string, array<string, mixed>> */
    private array $resolves = [];

    private int $l1Hits = 0;
    private int $l2Hits = 0;
    private int $cacheMisses = 0;

    private string $currentSource = 'direct';
    private string $currentCacheStatus = 'miss';

    /** @var array{file: ?string, line: ?int}|null */
    private ?array $callerOverride = null;

    public function setCurrentSource(?string $source): void
    {
        $this->currentSource = $source ?? 'direct';
    }

    public function getCurrentSource(): string
    {
        return $this->currentSource;
    }

    public function setCallerOverride(?string $callerFile, ?int $callerLine): void
    {
        $this->callerOverride = ['file' => $callerFile, 'line' => $callerLine];
    }

    /**
     * @return array{file: ?string, line: ?int}|null
     */
    public function consumeCallerOverride(): ?array
    {
        $override = $this->callerOverride;
        $this->callerOverride = null;

        return $override;
    }

    public function recordCacheStatus(string $status): void
    {
        $this->currentCacheStatus = $status;

        match ($status) {
            'l1' => $this->l1Hits++,
            'l2' => $this->l2Hits++,
            'miss' => $this->cacheMisses++,
            default => null,
        };
    }

    public function recordCheck(
        string $permission,
        string $resolveKey,
        bool $result,
        ?string $callerFile = null,
        ?int $callerLine = null,
    ): void {
        $this->checks[] = [
            'permission' => $permission,
            'resolveKey' => $resolveKey,
            'result' => $result,
            'source' => $this->currentSource,
            'cacheStatus' => $this->currentCacheStatus,
            'callerFile' => $callerFile,
            'callerLine' => $callerLine,
        ];

        // Reset cache status after recording
        $this->currentCacheStatus = 'miss';
    }

    /**
     * @param array<string, mixed> $trace
     */
    public function recordResolve(string $resolveKey, array $trace): void
    {
        $this->resolves[$resolveKey] = $trace;
    }

    /**
     * @return list<array{permission: string, resolveKey: string, result: bool, source: string, cacheStatus: string, callerFile: ?string, callerLine: ?int}>
     */
    public function getChecks(): array
    {
        return $this->checks;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getResolves(): array
    {
        return $this->resolves;
    }

    public function getL1Hits(): int
    {
        return $this->l1Hits;
    }

    public function getL2Hits(): int
    {
        return $this->l2Hits;
    }

    public function getCacheMisses(): int
    {
        return $this->cacheMisses;
    }
}
