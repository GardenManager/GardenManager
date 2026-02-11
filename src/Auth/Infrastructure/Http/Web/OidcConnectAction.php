<?php

declare(strict_types=1);

namespace GardenManager\Auth\Infrastructure\Http\Web;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class OidcConnectAction extends AbstractController
{
    public function __construct(
        private readonly ?string $oidcClientId,
    ) {
    }

    #[Route('/oidc/connect', name: 'app_oidc_connect')]
    public function __invoke(ClientRegistry $clientRegistry): RedirectResponse
    {
        if (empty($this->oidcClientId)) {
            throw new NotFoundHttpException();
        }

        return $clientRegistry->getClient('oidc')->redirect(['openid', 'email', 'profile']);
    }
}
