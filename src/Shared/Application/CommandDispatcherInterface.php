<?php

declare(strict_types=1);

namespace GardenManager\Shared\Application;

interface CommandDispatcherInterface
{
    public function dispatchCommand(CommandInterface $message): void;
}
