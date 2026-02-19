<?php

declare(strict_types=1);

namespace GardenManager\Shared\Infrastructure\Logging;

use GardenManager\Shared\Domain\Exception\Contract\ContextCarrierExceptionInterface;
use Monolog\Attribute\AsMonologProcessor;
use Monolog\LogRecord;
use Throwable;

#[AsMonologProcessor]
final class ExceptionContextLogProcessor
{
    public function __invoke(LogRecord $record): LogRecord
    {
        $exception = $record->context['exception'] ?? null;

        if (!$exception instanceof Throwable) {
            return $record;
        }

        $mergedContext = [];
        $current = $exception;

        while ($current !== null) {
            if ($current instanceof ContextCarrierExceptionInterface && !empty($current->getContext())) {
                $mergedContext = array_merge($current->getContext(), $mergedContext);
            }

            $current = $current->getPrevious();
        }

        if (empty($mergedContext)) {
            return $record;
        }

        $record->extra['exception_context'] = $mergedContext;

        return $record;
    }
}
