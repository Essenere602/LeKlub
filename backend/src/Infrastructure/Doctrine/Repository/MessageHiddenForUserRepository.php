<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\Message;
use App\Domain\Entity\MessageHiddenForUser;
use App\Domain\Entity\User;
use App\Domain\Repository\MessageHiddenForUserRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class MessageHiddenForUserRepository extends ServiceEntityRepository implements MessageHiddenForUserRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MessageHiddenForUser::class);
    }

    public function findForMessageAndUser(Message $message, User $user): ?MessageHiddenForUser
    {
        return $this->findOneBy([
            'message' => $message,
            'user' => $user,
        ]);
    }

    public function save(MessageHiddenForUser $hiddenMessage): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($hiddenMessage);
        $entityManager->flush();
    }
}
