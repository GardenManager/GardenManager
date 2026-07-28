<?php

declare(strict_types=1);

namespace GardenManager\Auth\Application\Query;

use GardenManager\Auth\Application\View\OidcLinkView;
use GardenManager\Auth\Domain\Persistence\AuthOidcRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class FindOidcLinkQueryHandler
{
    public function __construct(
        private AuthOidcRepositoryInterface $authOidcRepository,
    ) {
    }

    public function __invoke(FindOidcLinkQuery $query): ?OidcLinkView
    {
        $link = $this->authOidcRepository->findByProviderAndSubject($query->provider, $query->subject);

        if ($link === null) {
            return null;
        }

        return OidcLinkView::fromEntity($link);
    }
}
