<?php

declare(strict_types=1);

namespace GardenManager\Tests\Plant\Infrastructure\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use GardenManager\Auth\Domain\AuthUser;
use GardenManager\Plant\Domain\Entity\Plant;
use GardenManager\Plant\Domain\Enum\LifecycleEnum;
use GardenManager\Plant\Domain\Persistence\PlantRepositoryInterface;
use GardenManager\Shared\Domain\Exception\EntityNotFoundException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Ulid;

#[Group('integration')]
final class PlantRepositoryTest extends KernelTestCase
{
    private PlantRepositoryInterface $repository;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->repository = self::getContainer()->get(PlantRepositoryInterface::class);
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
    }

    #[Test]
    public function saveAndFindPlant(): void
    {
        $owner = $this->createOwner();

        $plant = Plant::create(
            tenantId: $owner->getId(),
            localName: 'Tomato',
            isHybrid: true,
            lifecycle: LifecycleEnum::ANNUAL,
            genus: 'Solanum',
            epithet: 'lycopersicum',
            cultivar: 'Roma',
        );
        $this->repository->save($plant);
        $this->em->flush();

        $found = $this->repository->getById($plant->getId());

        self::assertSame('Tomato', $found->getLocalName());
        self::assertTrue($found->isHybrid());
        self::assertSame(LifecycleEnum::ANNUAL, $found->getLifecycle());
        self::assertSame('Solanum', $found->getGenus());
        self::assertSame('lycopersicum', $found->getEpithet());
        self::assertSame('Roma', $found->getCultivar());
    }

    #[Test]
    public function softDeletedPlantIsFilteredOut(): void
    {
        $owner = $this->createOwner();

        $plant = Plant::create(
            tenantId: $owner->getId(),
            localName: 'Deleted Plant',
            isHybrid: false,
            lifecycle: LifecycleEnum::PERENNIAL,
        );
        $this->repository->save($plant);
        $this->em->flush();
        $id = $plant->getId();

        $plant->softDelete();
        $this->repository->save($plant);
        $this->em->flush();

        $this->expectException(EntityNotFoundException::class);
        $this->repository->getById($id);
    }

    #[Test]
    public function getByIdThrowsWhenNotFound(): void
    {
        $this->expectException(EntityNotFoundException::class);

        $this->repository->getById(new Ulid());
    }

    #[Test]
    public function findPaginatedReturnsPaginatedResult(): void
    {
        $owner = $this->createOwner();

        for ($i = 1; $i <= 3; ++$i) {
            $plant = Plant::create(
                tenantId: $owner->getId(),
                localName: "Plant $i",
                isHybrid: false,
                lifecycle: LifecycleEnum::ANNUAL,
            );
            $this->repository->save($plant);
            $this->em->flush();
        }

        $result = $this->repository->findPaginated(1, 2);

        self::assertGreaterThanOrEqual(3, $result->totalItems);
        self::assertCount(2, $result->items);
        self::assertTrue($result->hasNextPage());
    }

    private function createOwner(): AuthUser
    {
        $user = AuthUser::createWithPassword(
            new Ulid(),
            'owner-' . bin2hex(random_bytes(4)) . '@test.com',
            'Test Owner',
            'hashed',
        );

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}
