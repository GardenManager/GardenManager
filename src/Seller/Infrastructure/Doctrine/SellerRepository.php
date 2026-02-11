<?php

declare(strict_types=1);

namespace GardenManager\Seller\Infrastructure\Doctrine;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use GardenManager\Seller\Domain\Exception\SellerException;
use GardenManager\Seller\Domain\Seller;
use GardenManager\Seller\Domain\SellerRepositoryInterface;
use GardenManager\Shared\Domain\Pagination\PaginatedResult;
use GardenManager\Shared\Infrastructure\Pagination\PaginationFactory;
use Symfony\Component\Uid\Ulid;

/** @extends ServiceEntityRepository<Seller> */
final class SellerRepository extends ServiceEntityRepository implements SellerRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly PaginationFactory $paginationFactory,
    ) {
        parent::__construct($registry, Seller::class);
    }

    public function findById(Ulid $id): ?Seller
    {
        return $this->createQueryBuilder('s')
            ->where('s.id = :id')
            ->setParameter('id', $id, 'ulid')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getByIdForOwner(Ulid $id, Ulid $ownerId): Seller
    {
        $seller = $this->findById($id);

        if ($seller === null) {
            throw SellerException::notFoundById($id);
        }

        if (!$seller->isOwnedBy($ownerId)) {
            throw SellerException::notOwned($seller->getId(), $ownerId);
        }

        return $seller;
    }

    /** @return PaginatedResult<Seller> */
    public function findByOwnerIdPaginated(Ulid $ownerId, int $page, int $perPage): PaginatedResult
    {
        return $this->paginationFactory->createPaginatedResult(
            $this->ownerQueryBuilder($ownerId),
            $page,
            $perPage,
        );
    }

    public function save(Seller $seller): void
    {
        $this->getEntityManager()->persist($seller);
    }

    private function ownerQueryBuilder(Ulid $ownerId): QueryBuilder
    {
        return $this->createQueryBuilder('s')
            ->where('s.ownerId = :owner')
            ->setParameter('owner', $ownerId, 'ulid')
            ->orderBy('s.createdAt', 'DESC');
    }
}
