<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Domain;

final class MemberPermissions
{
    public const string LIST = 'member.list';
    public const string INVITE = 'member.invite';
    public const string REMOVE = 'member.remove';
}
