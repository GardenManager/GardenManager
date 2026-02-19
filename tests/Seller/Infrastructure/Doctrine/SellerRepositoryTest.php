<?php

declare(strict_types=1);

namespace GardenManager\Tests\Seller\Infrastructure\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use GardenManager\Auth\Domain\AuthUser;
use GardenManager\Seller\Domain\Seller;
use GardenManager\Seller\Domain\SellerRepositoryInterface;
use GardenManager\Shared\Domain\Exception\EntityNotFoundException;
use GardenManager\Shared\Domain\ValueObject\Address;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Ulid;

#[Group('integration')]
final class SellerRepositoryTest extends KernelTestCase
{
    private SellerRepositoryInterface $repository;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->repository = self::getContainer()->get(SellerRepositoryInterface::class);
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
    }

    #[Test]
    public function saveAndFindSeller(): void
    {
        $owner = $this->createOwner();

        $seller = Seller::create(
            name: 'John Garden',
            email: 'john@example.com',
            ownerId: $owner->getId(),
            address: new Address('123 Main St', 'Springfield', '62704', 'US'),
        );
        $this->repository->save($seller);
        $this->em->flush();

        $found = $this->repository->getById($seller->getId());

        self::assertSame('John Garden', $found->getName());
        self::assertSame('john@example.com', $found->getEmail());
        self::assertNotNull($found->getAddress());
        self::assertSame('123 Main St', $found->getAddress()->street);
    }

    #[Test]
    public function softDeletedSellerIsFilteredOut(): void
    {
        $owner = $this->createOwner();

        $seller = Seller::create(name: 'Deleted Seller', email: 'deleted@example.com', ownerId: $owner->getId());
        $this->repository->save($seller);
        $this->em->flush();
        $id = $seller->getId();

        $seller->softDelete();
        $this->repository->save($seller);
        $this->em->flush();

        $this->expectException(EntityNotFoundException::class);
        $this->repository->getById($id);
    }

    #[Test]
    public function findActiveByOwnerReturnsOnlyActiveSellers(): void
    {
        $owner = $this->createOwner();

        $seller1 = Seller::create(name: 'Active Seller', email: 'active@example.com', ownerId: $owner->getId());
        $this->repository->save($seller1);
        $this->em->flush();

        $seller2 = Seller::create(name: 'Deleted Seller', email: 'deleted2@example.com', ownerId: $owner->getId());
        $this->repository->save($seller2);
        $this->em->flush();
        $seller2->softDelete();
        $this->repository->save($seller2);
        $this->em->flush();

        $sellers = $this->repository->findByOwnerIdPaginated($owner->getId(), 1, 2)->items;

        $names = array_map(static fn (Seller $s): string => $s->getName(), $sellers);
        self::assertContains('Active Seller', $names);
        self::assertNotContains('Deleted Seller', $names);
    }

    #[Test]
    public function getByIdReturnsSeller(): void
    {
        $owner = $this->createOwner();

        $seller = Seller::create(name: 'Owned Seller', email: 'owned@example.com', ownerId: $owner->getId());
        $this->repository->save($seller);
        $this->em->flush();

        $found = $this->repository->getById($seller->getId());

        self::assertSame('Owned Seller', $found->getName());
    }

    #[Test]
    public function getByIdThrowsWhenNotFound(): void
    {
        $this->expectException(EntityNotFoundException::class);

        $this->repository->getById(new Ulid());
    }

    #[Test]
    public function findByOwnerIdPaginatedReturnsPaginatedResult(): void
    {
        $owner = $this->createOwner();

        for ($i = 1; $i <= 3; ++$i) {
            $seller = Seller::create(
                name: "Seller $i",
                email: "seller$i-" . bin2hex(random_bytes(4)) . '@test.com',
                ownerId: $owner->getId(),
            );
            $this->repository->save($seller);
            $this->em->flush();
        }

        $result = $this->repository->findByOwnerIdPaginated($owner->getId(), 1, 2);

        self::assertSame(3, $result->totalItems);
        self::assertSame(2, $result->totalPages());
        self::assertCount(2, $result->items);
        self::assertTrue($result->hasNextPage());
        self::assertFalse($result->hasPreviousPage());
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
