<?php

namespace GardenManager\Shared\Infrastructure\Form\DataTransformer;

use GardenManager\Shared\Application\Dto\AddressData;
use Symfony\Component\Form\DataTransformerInterface;

/** @implements DataTransformerInterface<AddressData|null, array|null> */
final class AddressTransformer implements DataTransformerInterface
{
    /** @param AddressData|null $value */
    public function transform(mixed $value): ?array
    {
        if ($value === null) {
            return null;
        }

        return $value->toArray();
    }

    /** @param array|null $value */
    public function reverseTransform(mixed $value): ?AddressData
    {
        if ($value === null) {
            return null;
        }

        $street = trim($value['street'] ?? '');
        $city = trim($value['city'] ?? '');
        $postalCode = trim($value['postalCode'] ?? '');
        $country = trim($value['country'] ?? '');

        if ($street === '' && $city === '' && $postalCode === '' && $country === '') {
            return null;
        }

        return new AddressData(
            street: $street,
            city: $city,
            postalCode: $postalCode,
            country: $country,
        );
    }
}
