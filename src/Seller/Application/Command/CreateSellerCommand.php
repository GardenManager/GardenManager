<?php

declare(strict_types=1);

namespace GardenManager\Seller\Application\Command;

use GardenManager\Seller\Domain\SellerPermissions;
use GardenManager\Shared\Application\Attribute\RequiresPermission;
use GardenManager\Shared\Application\AuthorizedMessageInterface;
use GardenManager\Shared\Application\CommandInterface;
use GardenManager\Shared\Application\Dto\AddressData;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[RequiresPermission(SellerPermissions::CREATE)]
final readonly class CreateSellerCommand implements CommandInterface, AuthorizedMessageInterface
{
    public function __construct(
        public Ulid $sellerId,
        public Ulid $tenantId,
        public Ulid $actorUserId,

        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $name,

        #[Assert\NotBlank]
        #[Assert\Email]
        #[Assert\Length(max: 255)]
        public string $email,

        #[Assert\Length(max: 50)]
        public ?string $phone = null,

        public ?string $description = null,

        public ?AddressData $address = null,
    ) {
    }

    public function getActorUserId(): Ulid
    {
        return $this->actorUserId;
    }

    public function getTenantId(): Ulid
    {
        return $this->tenantId;
    }
}
