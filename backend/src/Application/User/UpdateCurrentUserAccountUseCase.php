<?php

declare(strict_types=1);

namespace App\Application\User;

use App\Domain\Entity\User;
use App\Domain\Exception\DuplicateUserException;
use App\Domain\Repository\UserRepositoryInterface;
use App\DTO\User\UpdateAccountRequest;
use DomainException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UpdateCurrentUserAccountUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly GetCurrentUserProfileUseCase $getCurrentUserProfile,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(User $user, UpdateAccountRequest $request): array
    {
        $emailChanged = $request->email !== null && $request->email !== $user->getEmail();
        $usernameChanged = $request->username !== null && $request->username !== $user->getUsername();

        if (!$emailChanged && !$usernameChanged) {
            return $this->getCurrentUserProfile->execute($user);
        }

        if ($emailChanged) {
            if ($request->currentPassword === null || !$this->passwordHasher->isPasswordValid($user, $request->currentPassword)) {
                throw new DomainException('ACCOUNT_UPDATE_FAILED');
            }

            $existingUser = $this->users->findOneByEmail($request->email);
            if ($existingUser !== null && $existingUser !== $user) {
                throw DuplicateUserException::email();
            }

            $user->changeEmail($request->email);
        }

        if ($usernameChanged) {
            $existingUser = $this->users->findOneByUsername($request->username);
            if ($existingUser !== null && $existingUser !== $user) {
                throw DuplicateUserException::username();
            }

            $user->changeUsername($request->username);
        }

        $this->users->save($user);

        return $this->getCurrentUserProfile->execute($user);
    }
}
