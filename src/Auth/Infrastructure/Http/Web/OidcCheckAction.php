<?php

declare(strict_types=1);

namespace GardenManager\Auth\Infrastructure\Http\Web;

use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class OidcCheckAction extends AbstractController
{
    public function __construct(
        private readonly ?string $oidcClientId,
    ) {
    }

    #[Route('/oidc/check', name: 'app_oidc_check')]
    public function __invoke(): Response
    {
        if (empty($this->oidcClientId)) {
            throw new NotFoundHttpException();
        }

        throw new LogicException('This method should be intercepted by the OidcAuthenticator.');
    }
}
