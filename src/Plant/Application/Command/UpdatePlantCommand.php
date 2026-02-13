<?php

namespace GardenManager\Plant\Application\Command;

use GardenManager\Plant\Domain\Enum\LifecycleEnum;
use GardenManager\Shared\Application\CommandInterface;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

class UpdatePlantCommand implements CommandInterface
{
    public function __construct(
        public Ulid $plantId,
        public Ulid $ownerId,

        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $localName,

        #[Assert\Type(type: 'boolean')]
        public bool $isHybrid,

        public LifecycleEnum $lifecycle,

        #[Assert\Length(max: 64)]
        public ?string $genus = null,

        #[Assert\Length(max: 64)]
        public ?string $epithet = null,

        #[Assert\Length(max: 64)]
        public ?string $cultivar = null,
    ) {
    }
}
