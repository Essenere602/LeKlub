<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\SystemNotification;
use App\Domain\Entity\User;
use App\Domain\Repository\SystemNotificationRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class SystemNotificationRepository extends ServiceEntityRepository implements SystemNotificationRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SystemNotification::class);
    }

    public function save(SystemNotification $notification): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($notification);
        $entityManager->flush();
    }

    public function findForUser(int $id, User $user): ?SystemNotification
    {
        return $this->createQueryBuilder('notification')
            ->andWhere('notification.id = :id')
            ->andWhere('notification.recipient = :user')
            ->setParameter('id', $id)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function paginateForUser(User $user, int $page, int $limit): array
    {
        return $this->createQueryBuilder('notification')
            ->andWhere('notification.recipient = :user')
            ->setParameter('user', $user)
            ->orderBy('notification.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countForUser(User $user): int
    {
        return (int) $this->createQueryBuilder('notification')
            ->select('COUNT(notification.id)')
            ->andWhere('notification.recipient = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
