<?php

declare(strict_types=1);

namespace App\Application\Auth;

use App\Domain\Entity\PasswordResetToken;
use App\Domain\Repository\PasswordResetTokenRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\DTO\Auth\ForgotPasswordRequest;
use DateInterval;
use DateTimeImmutable;

final class RequestPasswordResetUseCase
{
    private const TOKEN_TTL = 'PT30M';

    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly PasswordResetTokenRepositoryInterface $tokens,
        private readonly PasswordResetNotifierInterface $notifier,
    ) {
    }

    public function execute(ForgotPasswordRequest $request): void
    {
        $user = $this->users->findOneByEmail($request->email);
        if ($user === null) {
            return;
        }

        $plainToken = bin2hex(random_bytes(32));
        $expiresAt = (new DateTimeImmutable())->add(new DateInterval(self::TOKEN_TTL));

        $this->tokens->invalidateActiveForUser($user);
        $this->tokens->save(new PasswordResetToken($user, self::hashToken($plainToken), $expiresAt));

        $this->notifier->notify($user, $plainToken, $expiresAt);
    }

    public static function hashToken(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }
}
