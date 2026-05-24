<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Application\Auth\PasswordResetNotifierInterface;
use App\Domain\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final class PasswordResetEmailNotifier implements PasswordResetNotifierInterface
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly LoggerInterface $logger,
        private readonly string $environment,
        private readonly bool $logTokenInDev,
        private readonly string $fromAddress,
        private readonly string $fromName,
    ) {
    }

    public function notify(User $user, string $plainToken, \DateTimeImmutable $expiresAt): void
    {
        $email = (new Email())
            ->from(new Address($this->fromAddress, $this->fromName))
            ->to($user->getEmail())
            ->subject('Réinitialisation de votre mot de passe LeKlub')
            ->text($this->message($plainToken, $expiresAt));

        $this->mailer->send($email);

        if ($this->environment === 'dev' && $this->logTokenInDev) {
            $this->logger->info('Password reset email sent for local development.', [
                'userId' => $user->getId(),
                'email' => $user->getEmail(),
                'token' => $plainToken,
                'expiresAt' => $expiresAt->format(DATE_ATOM),
            ]);
        }
    }

    private function message(string $plainToken, \DateTimeImmutable $expiresAt): string
    {
        return <<<TEXT
Bonjour,

Vous avez demandé la réinitialisation de votre mot de passe LeKlub.

Token de réinitialisation :
{$plainToken}

Ce token expire le {$expiresAt->format('d/m/Y à H:i')}.

Si vous n'êtes pas à l'origine de cette demande, ignorez simplement cet email.

LeKlub
TEXT;
    }
}
