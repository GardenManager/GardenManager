<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Infrastructure\Persistence;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use GardenManager\Shared\Domain\Exception\EntityNotFoundException;
use GardenManager\Tenant\Domain\Entity\TenantMembership;
use GardenManager\Tenant\Domain\Persistence\TenantMembershipRepositoryInterface;
use Symfony\Component\Uid\Ulid;

/** @extends ServiceEntityRepository<TenantMembership> */
final class TenantMembershipDoctrineRepository extends ServiceEntityRepository implements TenantMembershipRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TenantMembership::class);
    }

    public function getById(Ulid $id): TenantMembership
    {
        $membership = $this->createQueryBuilder('m')
            ->where('m.id = :id')
            ->setParameter('id', $id, 'ulid')
            ->getQuery()
            ->getOneOrNullResult();

        if ($membership === null) {
            throw EntityNotFoundException::fromEntityClassNameAndId(TenantMembership::class, $id);
        }

        return $membership;
    }

    public function findByTenantIdAndUserId(Ulid $tenantId, Ulid $userId): ?TenantMembership
    {
        return $this->createQueryBuilder('m')
            ->where('m.tenant = :tenantId')
            ->andWhere('m.userId = :userId')
            ->setParameter('tenantId', $tenantId, 'ulid')
            ->setParameter('userId', $userId, 'ulid')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /** @return list<TenantMembership> */
    public function findByTenantId(Ulid $tenantId): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.tenant = :tenantId')
            ->setParameter('tenantId', $tenantId, 'ulid')
            ->getQuery()
            ->getResult();
    }

    /** @return list<TenantMembership> */
    public function findByUserId(Ulid $userId): array
    {
        return $this->createQueryBuilder('m')
            ->join('m.tenant', 'tenant')
            ->where('m.userId = :userId')
            ->setParameter('userId', $userId, 'ulid')
            ->getQuery()
            ->getResult();
    }

    public function save(TenantMembership $membership): void
    {
        $this->getEntityManager()->persist($membership);
    }

    public function remove(TenantMembership $membership): void
    {
        $this->getEntityManager()->remove($membership);
    }
}
