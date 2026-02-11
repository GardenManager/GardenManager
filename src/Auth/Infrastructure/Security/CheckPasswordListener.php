<?php

namespace GardenManager\Auth\Infrastructure\Security;

use GardenManager\Auth\Domain\AuthUser;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

#[AsEventListener(event: CheckPassportEvent::class, priority: 256)]
final class CheckPasswordListener
{
    public function __invoke(CheckPassportEvent $event): void
    {
        $passport = $event->getPassport();

        if (!$passport->hasBadge(PasswordCredentials::class)) {
            return;
        }

        $user = $passport->getUser();

        if (!$user instanceof AuthUser) {
            return;
        }

        if (null === $user->getPassword()) {
            throw new CustomUserMessageAuthenticationException(
                'This account uses OpenID Connect login. Please use the "Login with OpenID" button instead.',
            );
        }
    }
}
