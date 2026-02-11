<?php

declare(strict_types=1);

namespace GardenManager\Shared\Domain\ValueObject;

use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Stringable;

#[ORM\Embeddable]
final readonly class EmailAddress implements Stringable
{
    #[ORM\Column(name: 'email', length: 255)]
    public string $value;

    public function __construct(string $value)
    {
        $value = trim($value);

        if (!filter_var($value, \FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(\sprintf('Invalid email address: "%s".', $value));
        }

        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
