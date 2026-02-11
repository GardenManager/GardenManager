<?php

namespace GardenManager\Shared\Application\View;

use GardenManager\Shared\Domain\ValueObject\Address;

final readonly class AddressView
{
    public function __construct(
        public ?string $street,
        public ?string $city,
        public ?string $postalCode,
        public ?string $country,
    ) {
    }

    public static function fromAddress(Address $address): self
    {
        return new self(
            street: $address->street,
            city: $address->city,
            postalCode: $address->postalCode,
            country: $address->country,
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
