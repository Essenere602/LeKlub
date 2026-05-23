<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Application\Auth\PasswordResetNotifierInterface;
use App\Domain\Entity\User;
use Psr\Log\LoggerInterface;

final class DevPasswordResetNotifier implements PasswordResetNotifierInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly string $environment,
    ) {
    }

    public function notify(User $user, string $plainToken, \DateTimeImmutable $expiresAt): void
    {
        if ($this->environment !== 'dev') {
            return;
        }

        $this->logger->info('Password reset token generated for local development.', [
            'userId' => $user->getId(),
            'email' => $user->getEmail(),
            'token' => $plainToken,
            'expiresAt' => $expiresAt->format(DATE_ATOM),
        ]);
    }
}
