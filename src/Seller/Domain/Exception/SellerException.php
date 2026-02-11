<?php

declare(strict_types=1);

namespace GardenManager\Seller\Domain\Exception;

use GardenManager\Shared\Domain\Exception\CoreException;
use Symfony\Component\Uid\Ulid;

final class SellerException extends CoreException
{
    public static function notFoundById(Ulid $sellerId): self
    {
        return new self(
            'Seller not found by ID',
            [
                'sellerId' => $sellerId,
            ],
            404,
        );
    }

    public static function notOwned(Ulid $sellerId, Ulid $requestUserId): self
    {
        return new self(
            'The seller not owned by the user',
            [
                'sellerId' => $sellerId,
                'requestUserId' => $requestUserId,
            ],
            404,
        );
    }
}
