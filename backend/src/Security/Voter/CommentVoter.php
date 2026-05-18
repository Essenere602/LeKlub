<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Domain\Entity\Comment;
use App\Domain\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class CommentVoter extends Voter
{
    public const DELETE = 'COMMENT_DELETE';
    public const EDIT = 'COMMENT_EDIT';

    public function __construct(
        private readonly Security $security,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::DELETE, self::EDIT], true) && $subject instanceof Comment;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        if ($attribute === self::DELETE && $this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return $user->getId() !== null && $subject->getAuthor()->getId() === $user->getId();
    }
}
