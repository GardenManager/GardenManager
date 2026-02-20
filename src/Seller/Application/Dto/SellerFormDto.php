<?php

declare(strict_types=1);

namespace GardenManager\Seller\Application\Dto;

use GardenManager\Seller\Application\View\SellerDetailView;
use GardenManager\Shared\Application\Dto\AddressData;
use Symfony\Component\Validator\Constraints as Assert;

final class SellerFormDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public ?string $name = null;

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 255)]
    public ?string $email = null;

    #[Assert\Length(max: 50)]
    public ?string $phone = null;

    public ?string $description = null;

    public ?AddressData $address = null;

    public static function fromView(SellerDetailView $view): self
    {
        $dto = new self();
        $dto->name = $view->name;
        $dto->email = $view->email;
        $dto->phone = $view->phone;
        $dto->description = $view->description;
        $dto->address = $view->address !== null
            ? new AddressData($view->address->street, $view->address->city, $view->address->postalCode, $view->address->country)
            : null;

        return $dto;
    }
}
