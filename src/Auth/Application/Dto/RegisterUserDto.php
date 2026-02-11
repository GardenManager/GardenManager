<?php

namespace GardenManager\Auth\Application\Dto;

final class RegisterUserDto
{
    public string $email;
    public string $displayName;
    public string $plainPassword;
}
