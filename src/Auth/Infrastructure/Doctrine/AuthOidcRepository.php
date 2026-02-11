<?php

declare(strict_types=1);

namespace GardenManager\Auth\Infrastructure\Doctrine;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use GardenManager\Auth\Domain\AuthOidc;
use GardenManager\Auth\Domain\AuthOidcRepositoryInterface;

/** @extends ServiceEntityRepository<AuthOidc> */
final class AuthOidcRepository extends ServiceEntityRepository implements AuthOidcRepositoryInterface
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
