<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\Conversation;
use App\Domain\Entity\Message;
use App\Domain\Entity\MessageHiddenForUser;
use App\Domain\Entity\User;
use App\Domain\Repository\MessageRepositoryInterface;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class MessageRepository extends ServiceEntityRepository implements MessageRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    public function save(Message $message): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($message);
        $entityManager->flush();
    }

    public function findForConversation(Conversation $conversation): array
    {
        return $this->createQueryBuilder('message')
            ->andWhere('message.conversation = :conversation')
            ->setParameter('conversation', $conversation)
            ->orderBy('message.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findVisibleForConversationAndUser(Conversation $conversation, User $user): array
    {
        return $this->createVisibleForUserQueryBuilder($conversation, $user)
            ->orderBy('message.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function markAsReadForRecipient(Conversation $conversation, User $recipient): void
    {
        $this->getEntityManager()->createQueryBuilder()
            ->update(Message::class, 'message')
            ->set('message.readAt', ':now')
            ->andWhere('message.conversation = :conversation')
            ->andWhere('message.sender != :recipient')
            ->andWhere('message.readAt IS NULL')
            ->setParameter('now', new DateTimeImmutable())
            ->setParameter('conversation', $conversation)
            ->setParameter('recipient', $recipient)
            ->getQuery()
            ->execute();
    }

    public function countUnreadForRecipient(Conversation $conversation, User $recipient): int
    {
        return (int) $this->createQueryBuilder('message')
            ->select('COUNT(message.id)')
            ->andWhere('message.conversation = :conversation')
            ->andWhere('message.sender != :recipient')
            ->andWhere('message.readAt IS NULL')
            ->setParameter('conversation', $conversation)
            ->setParameter('recipient', $recipient)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countVisibleUnreadForRecipient(Conversation $conversation, User $recipient): int
    {
        return (int) $this->createVisibleForUserQueryBuilder($conversation, $recipient)
            ->select('COUNT(message.id)')
            ->andWhere('message.sender != :recipient')
            ->andWhere('message.readAt IS NULL')
            ->setParameter('recipient', $recipient)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findLastForConversation(Conversation $conversation): ?Message
    {
        return $this->createQueryBuilder('message')
            ->andWhere('message.conversation = :conversation')
            ->setParameter('conversation', $conversation)
            ->orderBy('message.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findLastVisibleForConversationAndUser(Conversation $conversation, User $user): ?Message
    {
        return $this->createVisibleForUserQueryBuilder($conversation, $user)
            ->orderBy('message.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findById(int $id): ?Message
    {
        return $this->find($id);
    }

    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('message')
            ->select('COUNT(message.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function createVisibleForUserQueryBuilder(Conversation $conversation, User $user): \Doctrine\ORM\QueryBuilder
    {
        return $this->createQueryBuilder('message')
            ->leftJoin(
                MessageHiddenForUser::class,
                'hiddenMessage',
                'WITH',
                'hiddenMessage.message = message AND hiddenMessage.user = :user'
            )
            ->andWhere('message.conversation = :conversation')
            ->andWhere('hiddenMessage.id IS NULL')
            ->setParameter('conversation', $conversation)
            ->setParameter('user', $user);
    }
}
