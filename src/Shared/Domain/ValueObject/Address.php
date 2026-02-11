<?php

namespace GardenManager\Shared\Domain\ValueObject;

use Doctrine\ORM\Mapping as ORM;
use GardenManager\Shared\Application\Dto\AddressData;

#[ORM\Embeddable]
final readonly class Address
{
    #[ORM\Column(length: 255, nullable: true)]
    public ?string $street;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $city;

    #[ORM\Column(length: 20, nullable: true)]
    public ?string $postalCode;

    #[ORM\Column(length: 2, nullable: true)]
    public ?string $country;

    public function __construct(
        ?string $street,
        ?string $city,
        ?string $postalCode,
        ?string $country,
    ) {
        $this->street = $street;
        $this->city = $city;
        $this->postalCode = $postalCode;
        $this->country = $country;
    }

    public function isEmpty(): bool
    {
        return $this->street === null
            && $this->city === null
            && $this->postalCode === null
            && $this->country === null;
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
