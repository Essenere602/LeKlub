<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\User;
use App\Domain\Entity\UserWarning;
use App\Domain\Repository\UserWarningRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
}
