<?php

declare(strict_types=1);

namespace GardenManager\CustomAttribute\Infrastructure\Persistence;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use GardenManager\CustomAttribute\Domain\Entity\CustomAttributeDefinition;
use GardenManager\CustomAttribute\Domain\Exception\CustomAttributeException;
use GardenManager\CustomAttribute\Domain\Persistence\CustomAttributeDefinitionRepositoryInterface;
use GardenManager\Shared\Domain\Pagination\PaginatedResult;
use GardenManager\Shared\Infrastructure\Pagination\PaginationFactory;
use Override;
use Symfony\Component\Uid\Ulid;

/** @extends ServiceEntityRepository<CustomAttributeDefinition> */
final class CustomAttributeDefinitionDoctrineRepository
    extends ServiceEntityRepository
    implements CustomAttributeDefinitionRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly PaginationFactory $paginationFactory,
    ) {
        parent::__construct($registry, CustomAttributeDefinition::class);
    }

    public function getById(Ulid $id): CustomAttributeDefinition
    {
        $definition = $this->createQueryBuilder('d')
            ->where('d.id = :id')
            ->setParameter('id', $id, 'ulid')
            ->getQuery()
            ->getOneOrNullResult();

        if ($definition === null) {
            throw CustomAttributeException::definitionNotFound($id);
        }

        return $definition;
    }

    /** @return list<CustomAttributeDefinition> */
    public function findByEntityType(string $entityType): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.entityType = :entityType')
            ->setParameter('entityType', $entityType)
            ->orderBy('d.sortOrder', 'ASC')
            ->addOrderBy('d.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** @return PaginatedResult<CustomAttributeDefinition> */
    public function findPaginatedByEntityType(?string $entityType, int $page, int $perPage): PaginatedResult
    {
        $queryBuilder = $this->createQueryBuilder('d')
            ->orderBy('d.entityType', 'ASC')
            ->addOrderBy('d.sortOrder', 'ASC')
            ->addOrderBy('d.name', 'ASC');

        if ($entityType !== null) {
            $queryBuilder
                ->where('d.entityType = :entityType')
                ->setParameter('entityType', $entityType);
        }

        /** @var PaginatedResult<CustomAttributeDefinition> */
        return $this->paginationFactory->createPaginatedResult(
            $queryBuilder,
            $page,
            $perPage,
        );
    }

    public function existsByEntityTypeAndName(string $entityType, string $name): bool
    {
        $count = $this->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->where('d.entityType = :entityType')
            ->andWhere('d.name = :name')
            ->setParameter('entityType', $entityType)
            ->setParameter('name', $name)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $count > 0;
    }

    public function save(CustomAttributeDefinition $definition): void
    {
        $this->getEntityManager()->persist($definition);
    }

    public function remove(CustomAttributeDefinition $definition): void
    {
        $this->getEntityManager()->remove($definition);
    }
}
