<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\RefreshToken;
use App\Domain\Entity\User;
use App\Domain\Repository\RefreshTokenRepositoryInterface;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class RefreshTokenRepository extends ServiceEntityRepository implements RefreshTokenRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RefreshToken::class);
    }

    public function save(RefreshToken $refreshToken): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($refreshToken);
        $entityManager->flush();
    }

    public function findActiveByHash(string $tokenHash): ?RefreshToken
    {
        return $this->createQueryBuilder('token')
            ->andWhere('token.tokenHash = :tokenHash')
            ->andWhere('token.revokedAt IS NULL')
            ->andWhere('token.expiresAt > :now')
            ->setParameter('tokenHash', $tokenHash)
            ->setParameter('now', new DateTimeImmutable())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function revokeAllForUser(User $user): void
    {
        $this->createQueryBuilder('token')
            ->update()
            ->set('token.revokedAt', ':now')
            ->andWhere('token.user = :user')
            ->andWhere('token.revokedAt IS NULL')
            ->setParameter('user', $user)
            ->setParameter('now', new DateTimeImmutable())
            ->getQuery()
            ->execute();
    }
}
