<?php

declare(strict_types=1);

namespace GardenManager\Auth\Application\Query;

use GardenManager\Auth\Domain\AuthUserRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class FindUserByEmailQueryHandler
{
    public function __construct(
        private AuthUserRepositoryInterface $authUserRepository,
    ) {
    }

    public function __invoke(FindUserByEmailQuery $query): ?AuthUserSummaryView
    {
        $user = $this->authUserRepository->findByEmail($query->email);

        if ($user === null) {
            return null;
        }

        return AuthUserSummaryView::fromEntity($user);
    }
}
