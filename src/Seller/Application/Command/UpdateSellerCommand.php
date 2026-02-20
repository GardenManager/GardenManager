<?php

declare(strict_types=1);

namespace GardenManager\Seller\Application\Command;

use GardenManager\Shared\Application\CommandInterface;
use GardenManager\Shared\Application\Dto\AddressData;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateSellerCommand implements CommandInterface
{
    public function __construct(
        public Ulid $sellerId,
        public Ulid $tenantId,

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
}
