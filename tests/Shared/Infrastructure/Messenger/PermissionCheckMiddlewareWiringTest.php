<?php

declare(strict_types=1);

namespace GardenManager\Tests\Shared\Infrastructure\Messenger;

use GardenManager\Shared\Application\Attribute\NoPermissionRequired;
use GardenManager\Shared\Application\CommandInterface;
use GardenManager\Shared\Application\QueryInterface;
use GardenManager\Shared\Infrastructure\Messenger\Exception\MissingPermissionDeclarationException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\Exception\NoHandlerForMessageException;

#[Group('integration')]
final class PermissionCheckMiddlewareWiringTest extends KernelTestCase
{
    #[Test]
    public function commandBusRejectsMessagesWithoutAuthorizationPolicy(): void
    {
        $bus = self::getContainer()->get('command.bus');

        $this->expectException(MissingPermissionDeclarationException::class);

        $bus->dispatch(new class implements CommandInterface {});
    }

    #[Test]
    public function queryBusRejectsMessagesWithoutAuthorizationPolicy(): void
    {
        $bus = self::getContainer()->get('query.bus');

        $this->expectException(MissingPermissionDeclarationException::class);

        $bus->dispatch(new class implements QueryInterface {});
    }

    #[Test]
    public function queryBusPassesExemptMessagesThroughThePermissionMiddleware(): void
    {
        $bus = self::getContainer()->get('query.bus');

        // Reaching "no handler" proves the exempt message traversed the permission middleware without rejection.
        $this->expectException(NoHandlerForMessageException::class);

        $bus->dispatch(new #[NoPermissionRequired(reason: 'Wiring test double for the exemption path.')] class implements QueryInterface {});
    }
}
