<?php

declare(strict_types=1);

namespace GardenManager\Shared\Infrastructure\Messenger;

use GardenManager\Permission\Domain\Exception\PermissionException;
use GardenManager\Permission\Domain\Service\PermissionResolverInterface;
use GardenManager\Permission\Infrastructure\Profiler\PermissionProfilerDataStore;
use GardenManager\Shared\Application\Attribute\RequiresPermission;
use GardenManager\Shared\Application\AuthorizedMessageInterface;
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

        if ($message instanceof AuthorizedMessageInterface) {
            $reflection = new ReflectionClass($message);
            $attributes = $reflection->getAttributes(RequiresPermission::class);

            if ($attributes !== []) {
                /** @var RequiresPermission $requiredPermission */
                $requiredPermission = $attributes[0]->newInstance();

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
            }
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
