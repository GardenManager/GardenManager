<?php

declare(strict_types=1);

namespace GardenManager\Seller\Application\Dto;

use GardenManager\Shared\Application\Dto\AddressData;
use Symfony\Component\Validator\Constraints as Assert;

final class CreateSellerDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $name = '';

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 255)]
    public string $email = '';

    #[Assert\Length(max: 50)]
    public ?string $phone = null;

    public ?string $description = null;

    public ?AddressData $address = null;
}
