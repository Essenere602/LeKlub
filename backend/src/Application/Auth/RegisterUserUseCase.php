<?php

declare(strict_types=1);

namespace App\Application\Auth;

use App\Domain\Entity\User;
use App\Domain\Entity\UserProfile;
use App\Domain\Exception\DuplicateUserException;
use App\Domain\Repository\UserRepositoryInterface;
use App\DTO\Auth\RegisterRequest;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class RegisterUserUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function execute(RegisterRequest $request): User
    {
        if ($this->users->findOneByEmail($request->email) !== null) {
            throw DuplicateUserException::email();
        }

        if ($this->users->findOneByUsername($request->username) !== null) {
            throw DuplicateUserException::username();
        }

        $user = new User(mb_strtolower($request->email), $request->username, '');
        $user->setPassword($this->passwordHasher->hashPassword($user, $request->password));
        new UserProfile($user);

        $this->users->save($user);

        return $user;
    }
}
