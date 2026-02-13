<?php

declare(strict_types=1);

namespace GardenManager\Auth\Infrastructure\Http\Web;

use GardenManager\Auth\Application\Command\ConfirmOidcLinkCommand;
use GardenManager\Auth\Application\Dto\PendingOidcLink;
use GardenManager\Auth\Domain\AuthUserRepositoryInterface;
use GardenManager\Auth\Domain\Exception\AuthException;
use GardenManager\Auth\Infrastructure\Form\AccountLinkFormType;
use GardenManager\Shared\Infrastructure\Bus\CommandDispatcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Ulid;

final class LinkAccountAction extends AbstractController
{
    public function __construct(
        private readonly CommandDispatcher $commandDispatcher,
        private readonly AuthUserRepositoryInterface $authUserRepository,
        private readonly Security $security,
    ) {
    }

    #[Route('/link-account', name: 'app_link_account', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): ?Response
    {
        $pendingLink = $request->getSession()->get(PendingOidcLink::SESSION_KEY);

        if (!$pendingLink instanceof PendingOidcLink) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(AccountLinkFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->commandDispatcher->dispatchCommand(
                    new ConfirmOidcLinkCommand(
                        linkId: new Ulid(),
                        email: $pendingLink->email,
                        plainPassword: $form->get('password')->getData(),
                        provider: $pendingLink->provider,
                        subject: $pendingLink->subject,
                    ),
                );

                $request->getSession()->remove(PendingOidcLink::SESSION_KEY);

                $user = $this->authUserRepository->findByEmail($pendingLink->email);

                return $this->security->login($user, 'form_login');
            } catch (AuthException) {
                $this->addFlash('error', 'Invalid password. Please try again.');

                return $this->redirectToRoute('app_link_account');
            }
        }

        return $this->render('security/link_account.html.twig', [
            'linkForm' => $form,
            'email' => $pendingLink->email,
        ]);
    }
}
