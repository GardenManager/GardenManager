<?php

declare(strict_types=1);

namespace GardenManager\CustomAttribute\Domain\Enum;

enum AttributeTypeEnum: string
{
    case STRING = 'string';
    case INTEGER = 'integer';
    case DECIMAL = 'decimal';
    case DATE = 'date';
    case BOOLEAN = 'boolean';
    case SELECT = 'select';
}
