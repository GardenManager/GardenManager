<?php

namespace GardenManager\Shared\Infrastructure\Bus;

use GardenManager\Shared\Application\QueryInterface;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

final readonly class QueryDispatcher
{
    public function __construct(
        private MessageBusInterface $queryBus,
    )
    {
    }

    /**
     * @template TResult
     * @param QueryInterface<TResult> $message
     * @return TResult
     */
    public function query(QueryInterface $message): mixed
    {
        try {
            $envelope = $this->queryBus->dispatch($message);

            return $envelope->last(HandledStamp::class)->getResult();
        } catch (HandlerFailedException $e) {
            throw $e->getWrappedExceptions()[array_key_first($e->getWrappedExceptions())] ?? $e;
        }
    }
}
