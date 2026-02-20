<?php

declare(strict_types=1);

namespace GardenManager\Shared\Infrastructure\Bus;

use GardenManager\Shared\Application\CommandDispatcherInterface;
use GardenManager\Shared\Application\CommandInterface;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class CommandDispatcher implements CommandDispatcherInterface
{
    public function __construct(
        private MessageBusInterface $commandBus,
    ) {
    }

    public function dispatchCommand(CommandInterface $message): void
    {
        try {
            $this->commandBus->dispatch($message);
        } catch (HandlerFailedException $e) {
            throw $e->getWrappedExceptions()[array_key_first($e->getWrappedExceptions())] ?? $e;
        }
    }
}
