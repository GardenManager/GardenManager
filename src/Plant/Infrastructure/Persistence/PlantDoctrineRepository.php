<?php

declare(strict_types=1);

namespace GardenManager\Plant\Infrastructure\Persistence;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use GardenManager\Plant\Domain\Entity\Plant;
use GardenManager\Plant\Domain\Persistence\PlantRepositoryInterface;
use GardenManager\Shared\Domain\Exception\EntityNotFoundException;
use GardenManager\Shared\Domain\Pagination\PaginatedResult;
use GardenManager\Shared\Infrastructure\Pagination\PaginationFactory;
use Symfony\Component\Uid\Ulid;

/** @extends ServiceEntityRepository<Plant> */
final class PlantDoctrineRepository extends ServiceEntityRepository implements PlantRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly PaginationFactory $paginationFactory,
    ) {
        parent::__construct($registry, Plant::class);
    }

    public function getById(Ulid $plantId): Plant
    {
        $plant = $this->createQueryBuilder('plant')
            ->where('plant.id = :plantId')
            ->setParameter('plantId', $plantId, 'ulid')
            ->getQuery()
            ->getOneOrNullResult();

        if ($plant === null) {
            throw EntityNotFoundException::fromEntityClassNameAndId(Plant::class, $plantId);
        }

        return $plant;
    }

    /** @return PaginatedResult<Plant> */
    public function findPaginated(int $page, int $limit): PaginatedResult
    {
        $queryBuilder = $this->createQueryBuilder('plant')
            ->orderBy('plant.createdAt', 'DESC')
            ->addOrderBy('plant.id', 'DESC');

        /** @var PaginatedResult<Plant> */
        return $this->paginationFactory->createPaginatedResult(
            $queryBuilder,
            $page,
            $limit,
        );
    }

    public function save(Plant $plant): void
    {
        $this->getEntityManager()->persist($plant);
    }
}
