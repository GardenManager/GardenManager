<?php

declare(strict_types=1);

namespace GardenManager\Auth\Domain;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity]
#[ORM\Table(name: 'auth_oidc')]
#[ORM\UniqueConstraint(name: 'uq_provider_subject', columns: ['provider', 'subject'])]
#[ORM\Index(name: 'idx_user_id', columns: ['user_id'])]
class AuthOidc
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column(type: 'ulid', unique: true)]
    private Ulid $id;

    #[ORM\ManyToOne(targetEntity: AuthUser::class, inversedBy: 'oidcLinks')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private AuthUser $user;

    #[ORM\Column(length: 255)]
    private string $provider;

    #[ORM\Column(length: 255)]
    private string $subject;

    #[ORM\Column]
    private DateTimeImmutable $linkedAt;

    private function __construct()
    {
        $this->linkedAt = new DateTimeImmutable();
    }

    public static function create(Ulid $id, AuthUser $user, string $provider, string $subject): self
    {
        $link = new self();
        $link->id = $id;
        $link->user = $user;
        $link->provider = $provider;
        $link->subject = $subject;

        return $link;
    }

    public function getId(): Ulid
    {
        return $this->id;
    }

    public function getUser(): AuthUser
    {
        return $this->user;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getLinkedAt(): DateTimeImmutable
    {
        return $this->linkedAt;
    }
}
