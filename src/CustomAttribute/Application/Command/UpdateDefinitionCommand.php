<?php

declare(strict_types=1);

namespace GardenManager\CustomAttribute\Application\Command;

use GardenManager\CustomAttribute\Domain\CustomAttributePermissions;
use GardenManager\Shared\Application\Attribute\RequiresPermission;
use GardenManager\Shared\Application\AuthorizedMessageInterface;
use GardenManager\Shared\Application\CommandInterface;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[RequiresPermission(CustomAttributePermissions::EDIT)]
final readonly class UpdateDefinitionCommand implements CommandInterface, AuthorizedMessageInterface
{
    /**
     * @param list<string>|null $options
     */
    public function __construct(
        public Ulid $definitionId,
        public Ulid $tenantId,
        public Ulid $actorUserId,

        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $label,

        public bool $required = false,
        public int $sortOrder = 0,
        public ?array $options = null,
    ) {
    }

    public function getActorUserId(): Ulid
    {
        return $this->actorUserId;
    }

    public function getTenantId(): Ulid
    {
        return $this->tenantId;
    }
}
