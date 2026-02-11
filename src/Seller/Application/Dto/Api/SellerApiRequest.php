<?php

namespace GardenManager\Seller\Application\Dto\Api;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class SellerApiRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $name = '',

        #[Assert\NotBlank]
        #[Assert\Email]
        #[Assert\Length(max: 255)]
        public string $email = '',

        #[Assert\Length(max: 50)]
        public ?string $phone = null,

        public ?string $description = null,

        #[Assert\Valid]
        public ?AddressApiDto $address = null,
    ) {
    }
}
