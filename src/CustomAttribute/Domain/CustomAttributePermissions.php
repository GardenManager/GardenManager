<?php

declare(strict_types=1);

namespace GardenManager\CustomAttribute\Domain;

final class CustomAttributePermissions
{
    public const string VIEW = 'custom_attribute.view';
    public const string LIST = 'custom_attribute.list';
    public const string CREATE = 'custom_attribute.create';
    public const string EDIT = 'custom_attribute.edit';
    public const string DELETE = 'custom_attribute.delete';
    public const string SET_VALUES = 'custom_attribute.set_values';
}
