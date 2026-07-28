<?php

declare(strict_types=1);

namespace GardenManager\Auth\Infrastructure\Persistence;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use GardenManager\Auth\Domain\AuthUser;
use GardenManager\Auth\Domain\AuthUserRepositoryInterface;
use GardenManager\Shared\Domain\Exception\EntityNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Uid\Ulid;

/** @extends ServiceEntityRepository<AuthUser> */
final class AuthUserDoctrineRepository extends ServiceEntityRepository implements AuthUserRepositoryInterface, PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuthUser::class);
    }

    public function findById(Ulid $id): ?AuthUser
    {
        return $this->find($id);
    }

    public function getById(Ulid $id): AuthUser
    {
        return $this->findById($id) ?? throw EntityNotFoundException::fromEntityClassNameAndId(AuthUser::class, $id);
    }

    /**
     * @param list<Ulid> $userIds
     *
     * @return array<string, AuthUser>
     */
    public function findByIds(array $userIds): array
    {
        $indexed = [];

        if (empty($userIds)) {
            return $indexed;
        }

        /** @var list<AuthUser> $users */
        $users = $this->createQueryBuilder('u')
            ->where('u.id IN (:ids)')
            ->setParameter('ids', array_map(static fn (Ulid $id): string => $id->toRfc4122(), $userIds))
            ->getQuery()
            ->getResult();

        foreach ($users as $user) {
            $indexed[$user->getId()->toString()] = $user;
        }

        return $indexed;
    }

    public function findByEmail(string $email): ?AuthUser
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function save(AuthUser $user): void
    {
        $this->getEntityManager()->persist($user);
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof AuthUser) {
            throw new UnsupportedUserException(\sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }
}
