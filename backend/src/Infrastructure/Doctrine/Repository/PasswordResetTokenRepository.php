<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\PasswordResetToken;
use App\Domain\Entity\User;
use App\Domain\Repository\PasswordResetTokenRepositoryInterface;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class PasswordResetTokenRepository extends ServiceEntityRepository implements PasswordResetTokenRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PasswordResetToken::class);
    }

    public function save(PasswordResetToken $token): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($token);
        $entityManager->flush();
    }

    public function findActiveByHash(string $tokenHash): ?PasswordResetToken
    {
        return $this->createQueryBuilder('token')
            ->andWhere('token.tokenHash = :tokenHash')
            ->andWhere('token.usedAt IS NULL')
            ->andWhere('token.expiresAt > :now')
            ->setParameter('tokenHash', $tokenHash)
            ->setParameter('now', new DateTimeImmutable())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function invalidateActiveForUser(User $user): void
    {
        $this->createQueryBuilder('token')
            ->update()
            ->set('token.usedAt', ':now')
            ->andWhere('token.user = :user')
            ->andWhere('token.usedAt IS NULL')
            ->andWhere('token.expiresAt > :now')
            ->setParameter('user', $user)
            ->setParameter('now', new DateTimeImmutable())
            ->getQuery()
            ->execute();
    }
}
