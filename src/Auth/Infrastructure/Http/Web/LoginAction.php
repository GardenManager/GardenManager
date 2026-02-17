<?php

declare(strict_types=1);

namespace GardenManager\Auth\Infrastructure\Http\Web;

use GardenManager\Auth\Infrastructure\Form\LoginFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class LoginAction extends AbstractController
{
    public function __construct(
        private readonly ?string $oidcClientId,
    ) {
    }

    #[Route('/login', name: 'app_login')]
    public function __invoke(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        $form = $this->createForm(LoginFormType::class, [
            'email' => $authenticationUtils->getLastUsername(),
        ]);

        return $this->render('security/login.html.twig', [
            'form' => $form,
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'oidc_enabled' => !empty($this->oidcClientId),
        ]);
    }
}
