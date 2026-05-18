<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Message;
use App\Domain\Entity\MessageHiddenForUser;
use App\Domain\Entity\User;

interface MessageHiddenForUserRepositoryInterface
{
    public function findForMessageAndUser(Message $message, User $user): ?MessageHiddenForUser;

    public function save(MessageHiddenForUser $hiddenMessage): void;
}
