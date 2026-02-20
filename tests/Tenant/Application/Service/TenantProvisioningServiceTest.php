<?php

declare(strict_types=1);

namespace GardenManager\Tests\Tenant\Application\Service;

use GardenManager\Shared\Application\CommandDispatcherInterface;
use GardenManager\Tenant\Application\Command\CreateTenantCommand;
use GardenManager\Tenant\Application\Service\TenantProvisioningService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
final class TenantProvisioningServiceTest extends TestCase
{
    #[Test]
    public function dispatchesCreateTenantCommandWithUserIdAsTenantId(): void
    {
        $userId = new Ulid();
        $dispatchedCommand = null;

        $dispatcher = $this->createMock(CommandDispatcherInterface::class);
        $dispatcher->expects(self::once())
            ->method('dispatchCommand')
            ->willReturnCallback(static function (CreateTenantCommand $command) use (&$dispatchedCommand): void {
                $dispatchedCommand = $command;
            });

        $service = new TenantProvisioningService($dispatcher);

        $service->provisionPersonalTenant($userId, "John's Garden");

        self::assertInstanceOf(CreateTenantCommand::class, $dispatchedCommand);
        self::assertTrue($userId->equals($dispatchedCommand->tenantId));
        self::assertTrue($userId->equals($dispatchedCommand->userId));
        self::assertSame("John's Garden", $dispatchedCommand->name);
    }
}
