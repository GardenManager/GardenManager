<?php

declare(strict_types=1);

namespace GardenManager\Tests\Shared\Domain\Security;

use GardenManager\Shared\Domain\Exception\TenantAccessException;
use GardenManager\Shared\Domain\Security\TenantAccessChecker;
use GardenManager\Shared\Domain\TenantScoped;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
final class TenantAccessCheckerTest extends TestCase
{
    private TenantAccessChecker $checker;

    protected function setUp(): void
    {
        $this->checker = new TenantAccessChecker();
    }

    #[Test]
    public function allowsAccessWhenTenantIdMatches(): void
    {
        $tenantId = new Ulid();
        $entity = $this->createTenantScopedEntity(new Ulid(), $tenantId);

        $this->checker->ensureTenantAccess($entity, $tenantId);

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function throwsWhenTenantIdDoesNotMatch(): void
    {
        $entityTenantId = new Ulid();
        $activeTenantId = new Ulid();
        $entity = $this->createTenantScopedEntity(new Ulid(), $entityTenantId);

        $this->expectException(TenantAccessException::class);

        $this->checker->ensureTenantAccess($entity, $activeTenantId);
    }

    private function createTenantScopedEntity(Ulid $id, Ulid $tenantId): TenantScoped
    {
        return new readonly class($id, $tenantId) implements TenantScoped {
            public function __construct(
                private Ulid $id,
                private Ulid $tenantId,
            ) {
            }

            public function getId(): Ulid
            {
                return $this->id;
            }

            public function getTenantId(): Ulid
            {
                return $this->tenantId;
            }
        };
    }
}
