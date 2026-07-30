<?php

declare(strict_types=1);

namespace GardenManager\Seller\Infrastructure\Persistence;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use GardenManager\Seller\Domain\Entity\Seller;
use GardenManager\Seller\Domain\Persistence\SellerRepositoryInterface;
use GardenManager\Shared\Domain\Exception\EntityNotFoundException;
use GardenManager\Shared\Domain\Pagination\PaginatedResult;
use GardenManager\Shared\Infrastructure\Pagination\PaginationFactory;
use Override;
use Symfony\Component\Uid\Ulid;

/** @extends ServiceEntityRepository<Seller> */
final class SellerDoctrineRepository extends ServiceEntityRepository implements SellerRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly PaginationFactory $paginationFactory,
    ) {
        parent::__construct($registry, Seller::class);
    }

    #[Override]
    public function getById(Ulid $id): Seller
    {
        $seller = $this->createQueryBuilder('seller')
            ->where('seller.id = :id')
            ->setParameter('id', $id, 'ulid')
            ->getQuery()
            ->getOneOrNullResult();

        if ($seller === null) {
            throw EntityNotFoundException::fromEntityClassNameAndId(Seller::class, $id);
        }

        return $seller;
    }

    /** @return PaginatedResult<Seller> */
    #[Override]
    public function findPaginated(int $page, int $perPage): PaginatedResult
    {
        $queryBuilder = $this->createQueryBuilder('seller')
            ->orderBy('seller.createdAt', 'DESC')
            ->addOrderBy('seller.id', 'DESC');

        /** @var PaginatedResult<Seller> */
        return $this->paginationFactory->createPaginatedResult(
            $queryBuilder,
            $page,
            $perPage,
        );
    }

    #[Override]
    public function save(Seller $seller): void
    {
        $this->getEntityManager()->persist($seller);
    }
}
