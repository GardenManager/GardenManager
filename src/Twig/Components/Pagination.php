<?php

declare(strict_types=1);

namespace GardenManager\Twig\Components;

use GardenManager\Shared\Domain\Pagination\PaginatedResult;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Pagination
{
    public PaginatedResult $pager;
    public string $route;

    /** @var array<string, mixed> */
    public array $routeParams = [];
}
