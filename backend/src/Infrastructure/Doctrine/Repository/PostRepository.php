<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\Post;
use App\Domain\Entity\PostReaction;
use App\Domain\Repository\AdminPostModerationRepositoryInterface;
use App\Domain\Repository\PostRepositoryInterface;
use App\Domain\ValueObject\AdminContentStatus;
use App\Domain\ValueObject\PostReactionType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

final class PostRepository extends ServiceEntityRepository implements PostRepositoryInterface, AdminPostModerationRepositoryInterface
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

    public function paginateForModeration(AdminContentStatus $status, ?string $query, int $page, int $limit): array
    {
        return $this->createModerationQueryBuilder($status, $query)
            ->orderBy('post.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countForModeration(AdminContentStatus $status, ?string $query): int
    {
        return (int) $this->createModerationQueryBuilder($status, $query)
            ->select('COUNT(post.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function createModerationQueryBuilder(AdminContentStatus $status, ?string $query): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('post')
            ->join('post.author', 'author')
            ->leftJoin('author.profile', 'profile')
            ->leftJoin('post.deletedBy', 'deletedBy')
            ->leftJoin('deletedBy.profile', 'deletedByProfile')
            ->addSelect('author', 'profile', 'deletedBy', 'deletedByProfile');

        if ($status === AdminContentStatus::Active) {
            $queryBuilder->andWhere('post.deletedAt IS NULL');
        }

        if ($status === AdminContentStatus::Deleted) {
            $queryBuilder->andWhere('post.deletedAt IS NOT NULL');
        }

        if ($query !== null && $query !== '') {
            $search = mb_strtolower($query);
            $queryBuilder
                ->andWhere('LOWER(post.content) LIKE :query OR LOWER(author.username) LIKE :query OR LOWER(profile.displayName) LIKE :query')
                ->setParameter('query', '%'.$search.'%');
        }

        return $queryBuilder;
    }
}
