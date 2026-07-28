<?php

declare(strict_types=1);

namespace GardenManager\Auth\Infrastructure\Persistence;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use GardenManager\Auth\Domain\Entity\AuthOidc;
use GardenManager\Auth\Domain\Persistence\AuthOidcRepositoryInterface;

/** @extends ServiceEntityRepository<AuthOidc> */
final class AuthOidcDoctrineRepository extends ServiceEntityRepository implements AuthOidcRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuthOidc::class);
    }

    public function findByProviderAndSubject(string $provider, string $subject): ?AuthOidc
    {
        return $this->findOneBy(['provider' => $provider, 'subject' => $subject]);
    }

    public function save(AuthOidc $link): void
    {
        $this->getEntityManager()->persist($link);
    }
}
