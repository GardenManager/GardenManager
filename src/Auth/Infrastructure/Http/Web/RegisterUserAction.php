<?php

declare(strict_types=1);

namespace GardenManager\Auth\Infrastructure\Http\Web;

use GardenManager\Auth\Application\Command\RegisterUserCommand;
use GardenManager\Auth\Application\Dto\RegisterUserDto;
use GardenManager\Auth\Domain\AuthUserRepositoryInterface;
use GardenManager\Auth\Infrastructure\Form\RegistrationFormType;
use GardenManager\Shared\Infrastructure\Bus\CommandDispatcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Ulid;

final class RegisterUserAction extends AbstractController
{
    public function __construct(
        private readonly CommandDispatcher $commandDispatcher,
        private readonly AuthUserRepositoryInterface $authUserRepository,
        private readonly Security $security,
        private readonly bool $requireEmailVerification,
    ) {
    }

    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        $dto = new RegisterUserDto();
        $form = $this->createForm(RegistrationFormType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userId = new Ulid();

            $this->commandDispatcher->dispatchCommand(
                new RegisterUserCommand(
                    userId: $userId,
                    email: $dto->email,
                    displayName: $dto->displayName,
                    plainPassword: $dto->plainPassword,
                ),
            );

            if ($this->requireEmailVerification) {
                $this->addFlash('success', 'A verification email has been sent. Please check your inbox.');

                return $this->redirectToRoute('app_login');
            }

            $user = $this->authUserRepository->findById($userId);

            return $this->security->login($user, 'form_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
