<?php

declare(strict_types=1);

namespace GardenManager\Plant\Domain;

final class PlantPermissions
{
    public const string VIEW = 'plant.view';
    public const string LIST = 'plant.list';
    public const string CREATE = 'plant.create';
    public const string EDIT = 'plant.edit';
    public const string DELETE = 'plant.delete';
}
