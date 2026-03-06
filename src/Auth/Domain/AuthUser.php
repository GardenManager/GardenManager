<?php

declare(strict_types=1);

namespace GardenManager\Auth\Domain;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity]
#[ORM\Table(name: 'auth_user')]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(name: 'uq_email', columns: ['email'])]
class AuthUser implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column(type: 'ulid', unique: true)]
    private Ulid $id;

    #[ORM\Column(length: 180, unique: true)]
    private string $email;

    #[ORM\Column(length: 255)]
    private string $displayName;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $password = null;

    /** @var list<string> */
    #[ORM\Column(type: Types::JSON)]
    private array $roles = [];

    #[ORM\Column]
    private bool $isVerified = false;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    #[ORM\Column]
    private DateTimeImmutable $updatedAt;

    /** @var Collection<int, AuthOidc> */
    #[ORM\OneToMany(targetEntity: AuthOidc::class, mappedBy: 'user', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $oidcLinks;

    private function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
        $this->oidcLinks = new ArrayCollection();
    }

    public static function createWithoutPassword(Ulid $id, string $email, string $displayName): self
    {
        $user = new self();
        $user->id = $id;
        $user->email = $email;
        $user->displayName = $displayName;
        $user->isVerified = false;

        return $user;
    }

    public static function createWithPassword(
        Ulid $id,
        string $email,
        string $displayName,
        string $hashedPassword,
    ): self {
        $user = new self();
        $user->id = $id;
        $user->email = $email;
        $user->displayName = $displayName;
        $user->isVerified = false;
        $user->password = $hashedPassword;

        return $user;
    }

    public static function createFromOidc(Ulid $id, string $email, string $displayName): self
    {
        $user = new self();
        $user->id = $id;
        $user->email = $email;
        $user->displayName = $displayName;
        $user->password = null;
        $user->isVerified = true;

        return $user;
    }

    public function getId(): Ulid
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /** @return list<string> */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_values(array_unique($roles));
    }

    /** @param list<string> $roles */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function hasPassword(): bool
    {
        return $this->password !== null;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function verify(): void
    {
        $this->isVerified = true;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}
