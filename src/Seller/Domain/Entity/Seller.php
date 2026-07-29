<?php

declare(strict_types=1);

namespace GardenManager\Seller\Domain\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use GardenManager\Shared\Domain\SoftDeletable;
use GardenManager\Shared\Domain\TenantScoped;
use GardenManager\Shared\Domain\ValueObject\Address;
use GardenManager\Shared\Domain\ValueObject\EmailAddress;
use GardenManager\Shared\Domain\ValueObject\PhoneNumber;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity]
#[ORM\Table(name: 'seller')]
#[ORM\HasLifecycleCallbacks]
final class Seller implements SoftDeletable, TenantScoped
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column(type: 'ulid', unique: true)]
    private Ulid $id;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Embedded(class: EmailAddress::class, columnPrefix: false)]
    private EmailAddress $email;

    #[ORM\Embedded(class: PhoneNumber::class, columnPrefix: false)]
    private PhoneNumber $phone;

    #[ORM\Embedded(class: Address::class, columnPrefix: 'address_')]
    private ?Address $address = null;

    #[ORM\Column(type: 'ulid')]
    private Ulid $tenantId;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    #[ORM\Column]
    private DateTimeImmutable $updatedAt;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $deletedAt = null;

    private function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public static function create(
        string $name,
        string $email,
        Ulid $tenantId,
        ?string $phone = null,
        ?string $description = null,
        ?Address $address = null,
        ?Ulid $sellerId = null,
    ): self {
        $seller = new self();

        $seller->id = $sellerId ?? new Ulid();
        $seller->name = $name;
        $seller->email = new EmailAddress($email);
        $seller->phone = new PhoneNumber($phone);
        $seller->description = $description;
        $seller->address = $address;
        $seller->tenantId = $tenantId;

        return $seller;
    }

    public function update(
        string $name,
        string $email,
        ?string $phone = null,
        ?string $description = null,
        ?Address $address = null,
    ): void {
        $this->name = $name;
        $this->email = new EmailAddress($email);
        $this->phone = new PhoneNumber($phone);
        $this->description = $description;
        $this->address = $address;
    }

    public function getId(): Ulid
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getEmail(): string
    {
        return $this->email->value;
    }

    public function getPhone(): ?string
    {
        return $this->phone->value;
    }

    // This works around Doctrine hydrating embeddables with all-null columns as a non-null object
    public function getAddress(): ?Address
    {
        if ($this->address !== null && $this->address->isEmpty()) {
            return null;
        }

        return $this->address;
    }

    public function getTenantId(): Ulid
    {
        return $this->tenantId;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getDeletedAt(): ?DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function softDelete(): void
    {
        $this->deletedAt = new DateTimeImmutable();
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}
