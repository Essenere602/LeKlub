<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\Post;
use App\Domain\Entity\PostReaction;
use App\Domain\Repository\PostRepositoryInterface;
use App\Domain\ValueObject\PostReactionType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class PostRepository extends ServiceEntityRepository implements PostRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function save(Post $post): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($post);
        $entityManager->flush();
    }

    public function findVisibleById(int $id): ?Post
    {
        return $this->createQueryBuilder('post')
            ->andWhere('post.id = :id')
            ->andWhere('post.deletedAt IS NULL')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function paginateVisible(int $page, int $limit): array
    {
        return $this->createQueryBuilder('post')
            ->andWhere('post.deletedAt IS NULL')
            ->orderBy('post.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countVisible(): int
    {
        return (int) $this->createQueryBuilder('post')
            ->select('COUNT(post.id)')
            ->andWhere('post.deletedAt IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function paginateVisibleForAdmin(int $page, int $limit): array
    {
        return $this->createQueryBuilder('post')
            ->join('post.author', 'author')
            ->leftJoin('author.profile', 'profile')
            ->addSelect('author', 'profile')
            ->andWhere('post.deletedAt IS NULL')
            ->orderBy('post.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countVisibleComments(Post $post): int
    {
        return (int) $this->getEntityManager()->createQueryBuilder()
            ->select('COUNT(comment.id)')
            ->from(\App\Domain\Entity\Comment::class, 'comment')
            ->andWhere('comment.post = :post')
            ->andWhere('comment.deletedAt IS NULL')
            ->setParameter('post', $post)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countReactions(Post $post, PostReactionType $type): int
    {
        return (int) $this->getEntityManager()->createQueryBuilder()
            ->select('COUNT(reaction.id)')
            ->from(PostReaction::class, 'reaction')
            ->andWhere('reaction.post = :post')
            ->andWhere('reaction.type = :type')
            ->setParameter('post', $post)
            ->setParameter('type', $type)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
