<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Domain\Entity\Conversation;
use App\Domain\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class ConversationVoter extends Voter
{
    public const VIEW = 'CONVERSATION_VIEW';
    public const SEND_MESSAGE = 'CONVERSATION_SEND_MESSAGE';
    public const MARK_READ = 'CONVERSATION_MARK_READ';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::SEND_MESSAGE, self::MARK_READ], true)
            && $subject instanceof Conversation;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        return $user instanceof User && $subject->includes($user);
    }
}
