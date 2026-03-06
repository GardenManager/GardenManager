<?php

declare(strict_types=1);

namespace GardenManager\CustomAttribute\Infrastructure\Persistence;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use GardenManager\CustomAttribute\Domain\Entity\CustomAttributeValue;
use GardenManager\CustomAttribute\Domain\Persistence\CustomAttributeValueRepositoryInterface;
use Symfony\Component\Uid\Ulid;

/** @extends ServiceEntityRepository<CustomAttributeValue> */
final class CustomAttributeValueDoctrineRepository extends ServiceEntityRepository implements CustomAttributeValueRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CustomAttributeValue::class);
    }

    /** @return list<CustomAttributeValue> */
    public function findByEntityTypeAndEntityId(string $entityType, Ulid $entityId): array
    {
        return $this->createQueryBuilder('v')
            ->join('v.definition', 'd')
            ->where('v.entityType = :entityType')
            ->andWhere('v.entityId = :entityId')
            ->setParameter('entityType', $entityType)
            ->setParameter('entityId', $entityId, 'ulid')
            ->orderBy('d.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** @return array<string, CustomAttributeValue> keyed by definition ID string */
    public function findIndexedByDefinitionForEntity(string $entityType, Ulid $entityId): array
    {
        /** @var list<CustomAttributeValue> $values */
        $values = $this->createQueryBuilder('v')
            ->where('v.entityType = :entityType')
            ->andWhere('v.entityId = :entityId')
            ->setParameter('entityType', $entityType)
            ->setParameter('entityId', $entityId, 'ulid')
            ->getQuery()
            ->getResult();

        $indexed = [];
        foreach ($values as $value) {
            $indexed[(string) $value->getDefinition()->getId()] = $value;
        }

        return $indexed;
    }

    public function save(CustomAttributeValue $value): void
    {
        $this->getEntityManager()->persist($value);
    }

    public function remove(CustomAttributeValue $value): void
    {
        $this->getEntityManager()->remove($value);
    }
}
