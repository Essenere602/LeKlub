<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\User;
use App\Domain\Repository\AdminUserStatsRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class UserRepository extends ServiceEntityRepository implements UserRepositoryInterface, AdminUserStatsRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findOneByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => mb_strtolower($email)]);
    }

    public function findOneByUsername(string $username): ?User
    {
        return $this->findOneBy(['username' => $username]);
    }

    public function findById(int $id): ?User
    {
        return $this->find($id);
    }

    public function findForDirectory(User $currentUser, ?string $query, int $limit): array
    {
        $queryBuilder = $this->createQueryBuilder('user')
            ->leftJoin('user.profile', 'profile')
            ->andWhere('user != :currentUser')
            ->setParameter('currentUser', $currentUser)
            ->orderBy('user.username', 'ASC')
            ->setMaxResults($limit);

        if ($query !== null && $query !== '') {
            $search = mb_strtolower($query);
            $queryBuilder
                ->andWhere('LOWER(user.username) LIKE :query OR LOWER(profile.displayName) LIKE :query')
                ->setParameter('query', '%'.$search.'%');
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function paginateForAdmin(?string $query, int $page, int $limit): array
    {
        $queryBuilder = $this->createAdminQueryBuilder($query)
            ->orderBy('user.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return $queryBuilder->getQuery()->getResult();
    }

    public function countForAdmin(?string $query): int
    {
        return (int) $this->createAdminQueryBuilder($query)
            ->select('COUNT(user.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function save(User $user): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($user);
        $entityManager->flush();
    }

    public function countSuspendedUsers(): int
    {
        return (int) $this->createQueryBuilder('user')
            ->select('COUNT(user.id)')
            ->andWhere('user.suspendedUntil IS NOT NULL')
            ->andWhere('user.suspendedUntil > CURRENT_TIMESTAMP()')
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function createAdminQueryBuilder(?string $query): \Doctrine\ORM\QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('user')
            ->leftJoin('user.profile', 'profile')
            ->addSelect('profile');

        if ($query !== null && $query !== '') {
            $search = mb_strtolower($query);
            $queryBuilder
                ->andWhere('LOWER(user.username) LIKE :query OR LOWER(profile.displayName) LIKE :query')
                ->setParameter('query', '%'.$search.'%');
        }

        return $queryBuilder;
    }
}
