<?php

declare(strict_types=1);

namespace GardenManager\Auth\Infrastructure\Security;

use GardenManager\Auth\Application\Command\CreateOidcLinkCommand;
use GardenManager\Auth\Application\Command\ProvisionOidcUserCommand;
use GardenManager\Auth\Application\Dto\PendingOidcLink;
use GardenManager\Auth\Application\Query\FindOidcLinkQuery;
use GardenManager\Auth\Application\Query\FindUserByEmailQuery;
use GardenManager\Shared\Infrastructure\Bus\CommandDispatcher;
use GardenManager\Shared\Infrastructure\Bus\QueryDispatcher;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\GenericResourceOwner;
use Override;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Uid\Ulid;

final class OidcAuthenticator extends AbstractAuthenticator
{
    private const string LINK_REQUIRED_SENTINEL = '__LINK_REQUIRED__';

    public function __construct(
        private readonly ClientRegistry $clientRegistry,
        private readonly CommandDispatcher $commandDispatcher,
        private readonly QueryDispatcher $queryDispatcher,
        private readonly RouterInterface $router,
        private readonly ?string $oidcClientId,
    ) {
    }

    #[Override]
    public function supports(Request $request): bool
    {
        if (empty($this->oidcClientId)) {
            return false;
        }

        return $request->attributes->get('_route') === 'app_oidc_check';
    }

    #[Override]
    public function authenticate(Request $request): SelfValidatingPassport
    {
        $client = $this->clientRegistry->getClient('oidc');

        $accessToken = $client->getAccessToken();

        /** @var GenericResourceOwner $resourceOwner */
        $resourceOwner = $client->fetchUserFromToken($accessToken);
        $data = $resourceOwner->toArray();

        $email = $data['email'] ?? null;
        $name = $data['name'] ?? $data['preferred_username'] ?? $email;
        $subject = (string) ($data['sub'] ?? $resourceOwner->getId());

        if (null === $email) {
            throw new CustomUserMessageAuthenticationException('No email address was provided by the OpenID Connect provider.');
        }

        $linkView = $this->queryDispatcher->query(new FindOidcLinkQuery('oidc', $subject));

        if ($linkView !== null) {
            return new SelfValidatingPassport(new UserBadge($linkView->userEmail));
        }

        $userView = $this->queryDispatcher->query(new FindUserByEmailQuery($email));

        if ($userView !== null && $userView->hasPassword) {
            $request->getSession()->set(PendingOidcLink::SESSION_KEY, new PendingOidcLink(
                email: $email,
                provider: 'oidc',
                subject: $subject,
            ));

            throw new CustomUserMessageAuthenticationException(self::LINK_REQUIRED_SENTINEL);
        }

        if ($userView !== null) {
            $this->commandDispatcher->dispatchCommand(
                new CreateOidcLinkCommand(new Ulid(), $userView->id, 'oidc', $subject),
            );

            return new SelfValidatingPassport(new UserBadge($userView->email));
        }

        $this->commandDispatcher->dispatchCommand(
            new ProvisionOidcUserCommand(new Ulid(), new Ulid(), $email, $name, 'oidc', $subject),
        );

        return new SelfValidatingPassport(new UserBadge($email));
    }

    #[Override]
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): RedirectResponse
    {
        return new RedirectResponse($this->router->generate('app_dashboard'));
    }

    #[Override]
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): RedirectResponse
    {
        if ($exception->getMessage() === self::LINK_REQUIRED_SENTINEL) {
            return new RedirectResponse($this->router->generate('app_link_account'));
        }

        $request->getSession()->getFlashBag()->add('error', $exception->getMessageKey());

        return new RedirectResponse($this->router->generate('app_login'));
    }
}
