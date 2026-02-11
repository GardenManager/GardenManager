<?php

namespace GardenManager\Shared\Application\Dto;

use GardenManager\Shared\Domain\ValueObject\Address;

final readonly class AddressData
{
    public function __construct(
        public ?string $street,
        public ?string $city,
        public ?string $postalCode,
        public ?string $country,
    ) {
    }

    public function toAddress(): Address
    {
        return new Address(
            $this->street,
            $this->city,
            $this->postalCode,
            $this->country
        );
    }

    /** @return array{street: ?string, city: ?string, postalCode: ?string, country: ?string} */
    public function toArray(): array
    {
        return [
            'street' => $this->street,
            'city' => $this->city,
            'postalCode' => $this->postalCode,
            'country' => $this->country,
        ];
    }
}
