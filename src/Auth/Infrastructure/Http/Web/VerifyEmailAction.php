<?php

declare(strict_types=1);

namespace GardenManager\Auth\Infrastructure\Http\Web;

use Exception;
use GardenManager\Auth\Application\Command\VerifyEmailCommand;
use GardenManager\Auth\Application\EmailVerificationServiceInterface;
use GardenManager\Shared\Infrastructure\Bus\CommandDispatcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class VerifyEmailAction extends AbstractController
{
    public function __construct(
        private readonly CommandDispatcher $commandDispatcher,
        private readonly EmailVerificationServiceInterface $emailVerificationService,
    ) {
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function __invoke(Request $request): RedirectResponse
    {
        try {
            $userId = $this->emailVerificationService->validateEmailConfirmation($request);
            $this->commandDispatcher->dispatchCommand(new VerifyEmailCommand($userId));
            $this->addFlash('success', 'Your email address has been verified. You can now log in.');
        } catch (Exception) {
            $this->addFlash('error', 'The verification link is invalid or has expired.');
        }

        return $this->redirectToRoute('app_login');
    }
}
