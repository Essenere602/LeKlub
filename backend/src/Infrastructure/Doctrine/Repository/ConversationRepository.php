<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\Conversation;
use App\Domain\Entity\User;
use App\Domain\Repository\ConversationRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class ConversationRepository extends ServiceEntityRepository implements ConversationRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    public function save(Conversation $conversation): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($conversation);
        $entityManager->flush();
    }

    public function findById(int $id): ?Conversation
    {
        return $this->find($id);
    }

    public function findBetweenUsers(User $first, User $second): ?Conversation
    {
        return $this->createQueryBuilder('conversation')
            ->andWhere(
                '(conversation.participantOne = :first AND conversation.participantTwo = :second)
                OR (conversation.participantOne = :second AND conversation.participantTwo = :first)'
            )
            ->setParameter('first', $first)
            ->setParameter('second', $second)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findForUser(User $user): array
    {
        return $this->createQueryBuilder('conversation')
            ->andWhere('conversation.participantOne = :user OR conversation.participantTwo = :user')
            ->setParameter('user', $user)
            ->orderBy('conversation.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
