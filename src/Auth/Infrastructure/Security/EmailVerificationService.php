<?php

declare(strict_types=1);

namespace GardenManager\Auth\Infrastructure\Security;

use GardenManager\Auth\Application\EmailVerificationServiceInterface;
use GardenManager\Auth\Domain\AuthUser;
use GardenManager\Auth\Domain\AuthUserRepositoryInterface;
use GardenManager\Auth\Domain\Exception\AuthException;
use GardenManager\Auth\Domain\Exception\EmailVerificationException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Uid\Ulid;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

final class EmailVerificationService implements EmailVerificationServiceInterface
{
    public function __construct(
        private readonly VerifyEmailHelperInterface $verifyEmailHelper,
        private readonly MailerInterface $mailer,
        private readonly AuthUserRepositoryInterface $authUserRepository,
    ) {
    }

    public function sendVerificationEmail(AuthUser $user): void
    {
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            'app_verify_email',
            (string) $user->getId(),
            $user->getEmail(),
            ['id' => $user->getId()],
        );

        // TODO: Refactor email sending into its own service.
        $email = new TemplatedEmail()
            ->to(new Address($user->getEmail(), $user->getDisplayName()))
            ->subject('Please verify your email address')
            ->htmlTemplate('emails/verify_email.html.twig')
            ->context([
                'signedUrl' => $signatureComponents->getSignedUrl(),
                'expiresAtMessageKey' => $signatureComponents->getExpirationMessageKey(),
                'expiresAtMessageData' => $signatureComponents->getExpirationMessageData(),
                'user' => $user,
            ]);

        $this->mailer->send($email);
    }

    public function validateEmailConfirmation(Request $request): Ulid
    {
        $userId = $request->query->get('id');

        if ($userId === null) {
            throw EmailVerificationException::missingUserId();
        }

        $userUlid = Ulid::fromString($userId);
        $authUser = $this->authUserRepository->findById($userUlid);

        if ($authUser === null) {
            throw AuthException::userNotFoundById($userUlid);
        }

        try {
            $this->verifyEmailHelper->validateEmailConfirmationFromRequest(
                $request,
                (string) $userUlid,
                $authUser->getEmail(),
            );
        } catch (VerifyEmailExceptionInterface $e) {
            throw EmailVerificationException::invalidVerificationLink($e);
        }

        return $userUlid;
    }
}
