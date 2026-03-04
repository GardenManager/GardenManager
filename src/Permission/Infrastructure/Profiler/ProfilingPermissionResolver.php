<?php

declare(strict_types=1);

namespace GardenManager\Permission\Infrastructure\Profiler;

use GardenManager\Permission\Domain\Service\PermissionMatcher;
use GardenManager\Permission\Domain\Service\PermissionResolverInterface;
use GardenManager\Permission\Infrastructure\Cache\CachedPermissionResolver;
use GardenManager\Permission\Infrastructure\Twig\PermissionRuntime;
use GardenManager\Shared\Infrastructure\Messenger\PermissionCheckMiddleware;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\Uid\Ulid;
use Twig\Template;

#[When('dev')]
#[AsDecorator(decorates: PermissionResolverInterface::class, priority: -1)]
final readonly class ProfilingPermissionResolver implements PermissionResolverInterface
{
    public function __construct(
        #[AutowireDecorated]
        private PermissionResolverInterface $innerResolver,
        private PermissionProfilerDataStore $dataStore,
        private PermissionMatcher $matcher,
    ) {
    }

    public function hasPermission(Ulid $userId, Ulid $tenantId, string $permission): bool
    {
        $resolved = $this->resolvePermissions($userId, $tenantId);
        $result = $this->matcher->evaluate($resolved, $permission);

        $resolveKey = $tenantId->toString() . ':' . $userId->toString();
        $caller = $this->extractCallerLocation();

        $this->dataStore->recordCheck($permission, $resolveKey, $result, $caller['file'], $caller['line']);

        return $result;
    }

    /** @return array<string, bool> */
    public function resolvePermissions(Ulid $userId, Ulid $tenantId): array
    {
        $resolved = $this->innerResolver->resolvePermissions($userId, $tenantId);

        $resolveKey = $tenantId->toString() . ':' . $userId->toString();

        if (!isset($this->dataStore->getResolves()[$resolveKey])) {
            $this->dataStore->recordResolve($resolveKey, [
                'is_owner' => isset($resolved['**']) && $resolved['**'] === true,
                'from_cache' => true,
                'resolved_permissions' => $resolved,
            ]);
        }

        return $resolved;
    }

    /**
     * @return array{file: ?string, line: ?int}
     */
    private function extractCallerLocation(): array
    {
        $override = $this->dataStore->consumeCallerOverride();

        if ($override !== null) {
            return $override;
        }

        $trace = debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS | \DEBUG_BACKTRACE_PROVIDE_OBJECT);

        $skipClasses = [
            self::class,
            CachedPermissionResolver::class,
            PermissionRuntime::class,
            PermissionCheckMiddleware::class,
        ];

        // Look for a Twig\Template instance first (uses Twig internals intentionally)
        for ($i = 1, $count = \count($trace); $i < $count; ++$i) {
            if (isset($trace[$i]['object']) && $trace[$i]['object'] instanceof Template) {
                $template = $trace[$i]['object'];

                /** @psalm-suppress InternalMethod */
                $debugInfo = $template->getDebugInfo();
                /** @psalm-suppress PossiblyUndefinedArrayOffset - debug_backtrace frame may lack 'line' */
                $phpLine = $trace[$i - 1]['line'] ?? 0;

                foreach ($debugInfo as $codeLine => $templateLine) {
                    if ($codeLine <= $phpLine) {
                        /** @psalm-suppress InternalMethod */
                        return [
                            'file' => $template->getSourceContext()->getName(),
                            'line' => $templateLine,
                        ];
                    }
                }

                /** @psalm-suppress InternalMethod */
                return [
                    'file' => $template->getSourceContext()->getName(),
                    'line' => null,
                ];
            }
        }

        // No Twig — find first meaningful PHP caller
        foreach ($trace as $frame) {
            if (isset($frame['class']) && \in_array($frame['class'], $skipClasses, true)) {
                continue;
            }

            return [
                'file' => $frame['file'] ?? null,
                'line' => $frame['line'] ?? null,
            ];
        }

        return ['file' => null, 'line' => null];
    }
}
