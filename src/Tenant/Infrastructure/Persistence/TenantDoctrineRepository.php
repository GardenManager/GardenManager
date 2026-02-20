<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Infrastructure\Persistence;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use GardenManager\Shared\Domain\Exception\EntityNotFoundException;
use GardenManager\Tenant\Domain\Tenant;
use GardenManager\Tenant\Domain\TenantRepositoryInterface;
use Symfony\Component\Uid\Ulid;

/** @extends ServiceEntityRepository<Tenant> */
final class TenantDoctrineRepository extends ServiceEntityRepository implements TenantRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tenant::class);
    }

    public function getById(Ulid $id): Tenant
    {
        $tenant = $this->createQueryBuilder('tenant')
            ->where('tenant.id = :id')
            ->setParameter('id', $id, 'ulid')
            ->getQuery()
            ->getOneOrNullResult();

        if ($tenant === null) {
            throw EntityNotFoundException::fromEntityClassNameAndId(Tenant::class, $id);
        }

        return $tenant;
    }

    public function save(Tenant $tenant): void
    {
        $this->getEntityManager()->persist($tenant);
    }
}
