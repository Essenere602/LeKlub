<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\User;
use App\Domain\Entity\UserWarning;
use App\Domain\Repository\UserWarningRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

final class UserWarningRepository extends ServiceEntityRepository implements UserWarningRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserWarning::class);
    }

    public function save(UserWarning $warning): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($warning);
        $entityManager->flush();
    }

    public function countForUser(User $user): int
    {
        return (int) $this->createQueryBuilder('warning')
            ->select('COUNT(warning.id)')
            ->andWhere('warning.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function paginateForAdmin(int $page, int $limit, ?int $userId, bool $suspendedOnly): array
    {
        return $this->adminQueryBuilder($userId, $suspendedOnly)
            ->orderBy('warning.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countForAdmin(?int $userId, bool $suspendedOnly): int
    {
        return (int) $this->adminQueryBuilder($userId, $suspendedOnly)
            ->select('COUNT(warning.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function adminQueryBuilder(?int $userId, bool $suspendedOnly): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('warning')
            ->join('warning.user', 'warnedUser')
            ->join('warning.createdBy', 'createdBy')
            ->join('warning.report', 'report')
            ->leftJoin('warnedUser.profile', 'warnedProfile')
            ->leftJoin('createdBy.profile', 'createdByProfile')
            ->addSelect('warnedUser', 'createdBy', 'report', 'warnedProfile', 'createdByProfile');

        if ($userId !== null) {
            $queryBuilder
                ->andWhere('warnedUser.id = :userId')
                ->setParameter('userId', $userId);
        }

        if ($suspendedOnly) {
            $queryBuilder
                ->andWhere('warnedUser.suspendedUntil IS NOT NULL')
                ->andWhere('warnedUser.suspendedUntil > CURRENT_TIMESTAMP()');
        }

        return $queryBuilder;
    }
}
