<?php

namespace GardenManager\Plant\Application\Dto;

use GardenManager\Plant\Domain\Enum\LifecycleEnum;
use Symfony\Component\Validator\Constraints as Assert;

class PlantFormDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public ?string $localName = null;

    #[Assert\Type(type: 'boolean')]
    public ?bool $isHybrid = null;

    public ?LifecycleEnum $lifecycle = null;

    #[Assert\Length(max: 64)]
    public ?string $genus = null;

    #[Assert\Length(max: 64)]
    public ?string $epithet = null;

    #[Assert\Length(max: 64)]
    public ?string $cultivar = null;


}
