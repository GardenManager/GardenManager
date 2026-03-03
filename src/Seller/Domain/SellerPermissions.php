<?php

declare(strict_types=1);

namespace GardenManager\Seller\Domain;

final class SellerPermissions
{
    public const string VIEW = 'seller.view';
    public const string LIST = 'seller.list';
    public const string CREATE = 'seller.create';
    public const string EDIT = 'seller.edit';
    public const string DELETE = 'seller.delete';
}
