<?php

namespace GardenManager\Shared\Domain\ValueObject;

use Doctrine\ORM\Mapping as ORM;
use Stringable;

#[ORM\Embeddable]
final readonly class PhoneNumber implements Stringable
{
    #[ORM\Column(name: 'phone', length: 50, nullable: true)]
    public ?string $value;

    public function __construct(?string $value)
    {
        $trimmed = $value !== null ? trim($value) : null;
        $this->value = ($trimmed !== null && $trimmed !== '') ? $trimmed : null;
    }

    public function __toString(): string
    {
        return $this->value ?? '';
    }

    public function isEmpty(): bool
    {
        return $this->value === null;
    }
}
