<?php

declare(strict_types=1);

namespace GardenManager\CustomAttribute\Application\Dto;

use GardenManager\CustomAttribute\Application\View\DefinitionDetailView;
use GardenManager\CustomAttribute\Domain\Enum\AttributeTypeEnum;
use Symfony\Component\Validator\Constraints as Assert;

final class DefinitionFormDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 128)]
    #[Assert\Regex(pattern: '/^[a-z0-9_]+$/', message: 'Name must contain only lowercase letters, numbers, and underscores.')]
    public ?string $name = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public ?string $label = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 64)]
    public ?string $entityType = null;

    public ?AttributeTypeEnum $type = null;

    public ?bool $required = false;

    public ?int $sortOrder = 0;

    public ?string $optionsText = null;

    public static function fromView(DefinitionDetailView $view): self
    {
        $dto = new self();

        $dto->name = $view->name;
        $dto->label = $view->label;
        $dto->entityType = $view->entityType;
        $dto->type = $view->type;
        $dto->required = $view->required;
        $dto->sortOrder = $view->sortOrder;
        $dto->optionsText = $view->options !== null ? implode("\n", $view->options) : null;

        return $dto;
    }

    /** @return list<string>|null */
    public function getOptionsArray(): ?array
    {
        if ($this->optionsText === null || trim($this->optionsText) === '') {
            return null;
        }

        return array_values(array_filter(
            array_map(trim(...), explode("\n", $this->optionsText)),
            static fn (string $line): bool => $line !== '',
        ));
    }
}
