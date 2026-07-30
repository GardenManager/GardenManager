<?php

declare(strict_types=1);

namespace GardenManager\Shared\Infrastructure\Messenger;

use GardenManager\Permission\Domain\Exception\PermissionException;
use GardenManager\Permission\Domain\Service\PermissionResolverInterface;
use GardenManager\Permission\Infrastructure\Profiler\PermissionProfilerDataStore;
use GardenManager\Shared\Application\Attribute\NoPermissionRequired;
use GardenManager\Shared\Application\Attribute\RequiresPermission;
use GardenManager\Shared\Application\AuthorizedMessageInterface;
use GardenManager\Shared\Infrastructure\Messenger\Exception\MissingPermissionDeclarationException;
use ReflectionClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

final readonly class PermissionCheckMiddleware implements MiddlewareInterface
{
    public function __construct(
        private PermissionResolverInterface $permissionResolver,
        private ?PermissionProfilerDataStore $dataStore = null,
    ) {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $envelope->getMessage();
        $reflection = new ReflectionClass($message);

        $requiresPermission = $reflection->getAttributes(RequiresPermission::class);
        $noPermissionRequired = $reflection->getAttributes(NoPermissionRequired::class);

        if ($requiresPermission !== [] && $noPermissionRequired !== []) {
            throw MissingPermissionDeclarationException::conflictingDeclaration($message::class);
        }

        if ($noPermissionRequired !== []) {
            return $stack->next()->handle($envelope, $stack);
        }

        if ($requiresPermission === []) {
            throw MissingPermissionDeclarationException::missingDeclaration($message::class);
        }

        if (!$message instanceof AuthorizedMessageInterface) {
            throw MissingPermissionDeclarationException::missingAuthorizationContext($message::class);
        }

        $requiredPermission = $requiresPermission[0]->newInstance();

        $this->dataStore?->setCurrentSource('middleware');
        $this->dataStore?->setCallerOverride(
            $reflection->getFileName() ?: null,
            $reflection->getStartLine() ?: null,
        );

        try {
            if (!$this->permissionResolver->hasPermission(
                $message->getActorUserId(),
                $message->getTenantId(),
                $requiredPermission->permission,
            )) {
                throw PermissionException::accessDenied($requiredPermission->permission, $message->getActorUserId());
            }
        } finally {
            $this->dataStore?->setCurrentSource(null);
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
