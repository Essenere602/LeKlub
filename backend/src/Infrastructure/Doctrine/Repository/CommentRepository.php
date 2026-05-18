<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\Comment;
use App\Domain\Entity\Post;
use App\Domain\Repository\CommentRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class CommentRepository extends ServiceEntityRepository implements CommentRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public function save(Comment $comment): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($comment);
        $entityManager->flush();
    }

    public function findVisibleById(int $id): ?Comment
    {
        return $this->createQueryBuilder('comment')
            ->join('comment.post', 'post')
            ->andWhere('comment.id = :id')
            ->andWhere('comment.deletedAt IS NULL')
            ->andWhere('post.deletedAt IS NULL')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function paginateVisibleForPost(Post $post, int $page, int $limit): array
    {
        return $this->createQueryBuilder('comment')
            ->andWhere('comment.post = :post')
            ->andWhere('comment.deletedAt IS NULL')
            ->setParameter('post', $post)
            ->orderBy('comment.createdAt', 'ASC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countVisibleForPost(Post $post): int
    {
        return (int) $this->createQueryBuilder('comment')
            ->select('COUNT(comment.id)')
            ->andWhere('comment.post = :post')
            ->andWhere('comment.deletedAt IS NULL')
            ->setParameter('post', $post)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function paginateVisibleForAdmin(int $page, int $limit): array
    {
        return $this->createQueryBuilder('comment')
            ->join('comment.author', 'author')
            ->join('comment.post', 'post')
            ->leftJoin('author.profile', 'profile')
            ->addSelect('author', 'post', 'profile')
            ->andWhere('comment.deletedAt IS NULL')
            ->andWhere('post.deletedAt IS NULL')
            ->orderBy('comment.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countVisible(): int
    {
        return (int) $this->createQueryBuilder('comment')
            ->select('COUNT(comment.id)')
            ->join('comment.post', 'post')
            ->andWhere('comment.deletedAt IS NULL')
            ->andWhere('post.deletedAt IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
