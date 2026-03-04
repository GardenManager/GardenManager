<?php

declare(strict_types=1);

namespace GardenManager\Permission\Infrastructure\Console;

use GardenManager\Permission\Application\Service\PermissionCacheInvalidatorInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Uid\Ulid;

#[AsCommand(
    name: 'permission:cache:invalidate',
    description: 'Invalidate the permission cache',
)]
final class InvalidatePermissionCacheCommand extends Command
{
    public function __construct(
        private readonly PermissionCacheInvalidatorInterface $cacheInvalidator,
        #[Autowire(param: 'kernel.environment')]
        private readonly string $environment,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('tenantId', null, InputOption::VALUE_REQUIRED, 'Tenant ULID to scope invalidation')
            ->addOption('userId', null, InputOption::VALUE_REQUIRED, 'User ULID to scope invalidation (requires --tenant)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $tenantIdInput = $input->getOption('tenantId');
        $userIdInput = $input->getOption('userId');

        if ($userIdInput !== null && $tenantIdInput === null) {
            $io->error('The --userId option requires --tenantId to be specified');

            return Command::FAILURE;
        }

        if ($tenantIdInput !== null && !Ulid::isValid($tenantIdInput)) {
            $io->error(\sprintf('Invalid tenant ULID: %s', $tenantIdInput));

            return Command::FAILURE;
        }

        if ($userIdInput !== null && !Ulid::isValid($userIdInput)) {
            $io->error(\sprintf('Invalid user ULID: %s', $userIdInput));

            return Command::FAILURE;
        }

        if ($tenantIdInput !== null && $userIdInput !== null) {
            $this->cacheInvalidator->invalidateForUser(
                Ulid::fromString($userIdInput),
                Ulid::fromString($tenantIdInput),
            );
            $io->success(\sprintf('Permission cache invalidated for user %s in tenant %s.', $userIdInput, $tenantIdInput));

            return Command::SUCCESS;
        }

        if ($tenantIdInput !== null) {
            $this->cacheInvalidator->invalidateForTenant(Ulid::fromString($tenantIdInput));
            $io->success(\sprintf('Permission cache invalidated for tenant %s.', $tenantIdInput));

            return Command::SUCCESS;
        }

        if ($this->environment === 'prod') {
            $io->warning([
                'You are about to invalidate the ENTIRE permission cache!',
                'This will affect ALL tenants and users. Permissions will be re-resolved from the database on next access.',
            ]);

            if (!$io->confirm('Do you want to continue?', false)) {
                $io->info('Aborted.');

                return Command::SUCCESS;
            }
        }

        $this->cacheInvalidator->invalidateAll();
        $io->success('Permission cache invalidated for all tenants and users.');

        return Command::SUCCESS;
    }
}
