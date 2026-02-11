<?php

declare(strict_types=1);

namespace GardenManager\Seller\Application\Dto\Api;

use GardenManager\Shared\Application\Dto\AddressData;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class AddressApiDto
{
    public function __construct(
        #[Assert\NotBlank]
        public string $street = '',

        #[Assert\NotBlank]
        public string $city = '',

        #[Assert\NotBlank]
        public string $postalCode = '',

        #[Assert\NotBlank]
        #[Assert\Country]
        public string $country = '',
    ) {
    }

    public function toAddressData(): AddressData
    {
        return new AddressData(
            $this->street,
            $this->city,
            $this->postalCode,
            $this->country,
        );
    }
}
