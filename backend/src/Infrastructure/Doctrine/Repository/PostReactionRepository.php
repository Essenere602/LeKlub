<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\Post;
use App\Domain\Entity\PostReaction;
use App\Domain\Entity\User;
use App\Domain\Repository\PostReactionRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class PostReactionRepository extends ServiceEntityRepository implements PostReactionRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PostReaction::class);
    }

    public function save(PostReaction $reaction): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($reaction);
        $entityManager->flush();
    }

    public function remove(PostReaction $reaction): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->remove($reaction);
        $entityManager->flush();
    }

    public function findForUserAndPost(User $user, Post $post): ?PostReaction
    {
        return $this->findOneBy([
            'user' => $user,
            'post' => $post,
        ]);
    }
}
