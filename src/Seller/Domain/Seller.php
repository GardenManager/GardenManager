<?php

namespace GardenManager\Seller\Domain;

use Doctrine\ORM\Mapping as ORM;
use GardenManager\Shared\Domain\SoftDeletable;
use GardenManager\Shared\Domain\ValueObject\Address;
use GardenManager\Shared\Domain\ValueObject\EmailAddress;
use GardenManager\Shared\Domain\ValueObject\PhoneNumber;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity]
#[ORM\Table(name: 'seller')]
#[ORM\HasLifecycleCallbacks]
final class Seller implements SoftDeletable
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
    private Ulid $ownerId;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    private function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public static function create(
        string $name,
        string $email,
        Ulid $ownerId,
        ?string $phone = null,
        ?string $description = null,
        ?Address $address = null,
        ?Ulid $id = null,
    ): self {
        $seller = new self();
        $sellerId = $id;

        if ($sellerId === null) {
            $sellerId = new Ulid();
        }

        $seller->id = $sellerId;
        $seller->name = $name;
        $seller->email = new EmailAddress($email);
        $seller->phone = new PhoneNumber($phone);
        $seller->description = $description;
        $seller->address = $address;
        $seller->ownerId = $ownerId;

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

    public function getOwnerId(): Ulid
    {
        return $this->ownerId;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function softDelete(): void
    {
        $this->deletedAt = new \DateTimeImmutable();
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function isOwnedBy(Ulid $userId): bool
    {
        return $this->ownerId->equals($userId);
    }
}
