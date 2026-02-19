<?php

declare(strict_types=1);

namespace GardenManager\Tests\Shared\Infrastructure\Logging;

use GardenManager\Plant\Domain\Entity\Plant;
use GardenManager\Shared\Domain\Exception\Contract\ContextCarrierExceptionInterface;
use GardenManager\Shared\Domain\Exception\EntityNotFoundException;
use GardenManager\Shared\Infrastructure\Logging\ExceptionContextLogProcessor;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Uid\Ulid;
use Throwable;

#[Group('unit')]
final class ExceptionContextLogProcessorTest extends TestCase
{
    private ExceptionContextLogProcessor $processor;

    protected function setUp(): void
    {
        $this->processor = new ExceptionContextLogProcessor();
    }

    #[Test]
    public function noExceptionInContextLeavesExtraUnchanged(): void
    {
        $record = $this->createRecord([]);

        $result = ($this->processor)($record);

        self::assertArrayNotHasKey('exception_context', $result->extra);
    }

    #[Test]
    public function nonThrowableValueInContextLeavesExtraUnchanged(): void
    {
        $record = $this->createRecord(['exception' => 'not a throwable']);

        $result = ($this->processor)($record);

        self::assertArrayNotHasKey('exception_context', $result->extra);
    }

    #[Test]
    public function plainRuntimeExceptionLeavesExtraUnchanged(): void
    {
        $record = $this->createRecord(['exception' => new RuntimeException('message')]);

        $result = ($this->processor)($record);

        self::assertArrayNotHasKey('exception_context', $result->extra);
    }

    #[Test]
    public function entityNotFoundExceptionAddsContext(): void
    {
        $entityId = new Ulid();
        $exception = EntityNotFoundException::fromEntityClassNameAndId(Plant::class, $entityId);

        $record = $this->createRecord(['exception' => $exception]);

        $result = ($this->processor)($record);

        self::assertSame(
            [
                'fullyQualifiedClassName' => Plant::class,
                'entityId' => $entityId,
            ],
            $result->extra['exception_context'],
        );
    }

    #[Test]
    public function exceptionWithEmptyContextLeavesExtraUnchanged(): void
    {
        $exception = new class ('empty') extends RuntimeException implements ContextCarrierExceptionInterface {
            public function getContext(): array
            {
                return [];
            }
        };

        $record = $this->createRecord(['exception' => $exception]);

        $result = ($this->processor)($record);

        self::assertArrayNotHasKey('exception_context', $result->extra);
    }

    #[Test]
    public function chainedExceptionsMergeContext(): void
    {
        $inner = new class ('inner') extends RuntimeException implements ContextCarrierExceptionInterface {
            public function getContext(): array
            {
                return ['innerKey' => 'innerValue'];
            }
        };

        $outer = new class ('outer', $inner) extends RuntimeException implements ContextCarrierExceptionInterface {
            public function __construct(string $message, Throwable $previous)
            {
                parent::__construct($message, 0, $previous);
            }

            public function getContext(): array
            {
                return ['outerKey' => 'outerValue'];
            }
        };

        $record = $this->createRecord(['exception' => $outer]);

        $result = ($this->processor)($record);

        self::assertSame(
            [
                'innerKey' => 'innerValue',
                'outerKey' => 'outerValue',
            ],
            $result->extra['exception_context'],
        );
    }

    #[Test]
    public function overlappingKeysInChainOuterWins(): void
    {
        $inner = new class ('inner') extends RuntimeException implements ContextCarrierExceptionInterface {
            public function getContext(): array
            {
                return ['shared' => 'fromInner', 'innerOnly' => 'inner'];
            }
        };

        $outer = new class ('outer', $inner) extends RuntimeException implements ContextCarrierExceptionInterface {
            public function __construct(string $message, Throwable $previous)
            {
                parent::__construct($message, 0, $previous);
            }

            public function getContext(): array
            {
                return ['shared' => 'fromOuter', 'outerOnly' => 'outer'];
            }
        };

        $record = $this->createRecord(['exception' => $outer]);

        $result = ($this->processor)($record);

        self::assertSame(
            [
                'shared' => 'fromOuter',
                'innerOnly' => 'inner',
                'outerOnly' => 'outer',
            ],
            $result->extra['exception_context'],
        );
    }

    private function createRecord(array $context): LogRecord
    {
        return new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'test',
            level: Level::Error,
            message: 'test message',
            context: $context,
        );
    }
}
