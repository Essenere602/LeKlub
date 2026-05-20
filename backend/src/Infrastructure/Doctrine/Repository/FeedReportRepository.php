<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\FeedReport;
use App\Domain\Entity\User;
use App\Domain\Repository\FeedReportRepositoryInterface;
use App\Domain\ValueObject\FeedReportStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

final class FeedReportRepository extends ServiceEntityRepository implements FeedReportRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FeedReport::class);
    }

    public function save(FeedReport $report): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($report);
        $entityManager->flush();
    }

    public function findById(int $id): ?FeedReport
    {
        return $this->createQueryBuilder('report')
            ->leftJoin('report.reporter', 'reporter')
            ->leftJoin('report.post', 'post')
            ->leftJoin('report.comment', 'comment')
            ->leftJoin('post.author', 'postAuthor')
            ->leftJoin('comment.author', 'commentAuthor')
            ->leftJoin('comment.post', 'commentPost')
            ->leftJoin('report.resolvedBy', 'resolvedBy')
            ->addSelect('reporter', 'post', 'comment', 'postAuthor', 'commentAuthor', 'commentPost', 'resolvedBy')
            ->andWhere('report.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function existsForReporterAndPost(User $reporter, int $postId): bool
    {
        return (int) $this->createQueryBuilder('report')
            ->select('COUNT(report.id)')
            ->andWhere('report.reporter = :reporter')
            ->andWhere('report.post = :postId')
            ->setParameter('reporter', $reporter)
            ->setParameter('postId', $postId)
            ->getQuery()
            ->getSingleScalarResult() > 0;
    }

    public function existsForReporterAndComment(User $reporter, int $commentId): bool
    {
        return (int) $this->createQueryBuilder('report')
            ->select('COUNT(report.id)')
            ->andWhere('report.reporter = :reporter')
            ->andWhere('report.comment = :commentId')
            ->setParameter('reporter', $reporter)
            ->setParameter('commentId', $commentId)
            ->getQuery()
            ->getSingleScalarResult() > 0;
    }

    public function paginateForAdmin(int $page, int $limit, ?FeedReportStatus $status): array
    {
        return $this->adminQueryBuilder($status)
            ->orderBy('report.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countForAdmin(?FeedReportStatus $status): int
    {
        $queryBuilder = $this->createQueryBuilder('report')
            ->select('COUNT(report.id)');

        if ($status !== null) {
            $queryBuilder
                ->andWhere('report.status = :status')
                ->setParameter('status', $status);
        }

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    private function adminQueryBuilder(?FeedReportStatus $status): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('report')
            ->leftJoin('report.reporter', 'reporter')
            ->leftJoin('report.post', 'post')
            ->leftJoin('report.comment', 'comment')
            ->leftJoin('post.author', 'postAuthor')
            ->leftJoin('comment.author', 'commentAuthor')
            ->leftJoin('comment.post', 'commentPost')
            ->leftJoin('report.resolvedBy', 'resolvedBy')
            ->addSelect('reporter', 'post', 'comment', 'postAuthor', 'commentAuthor', 'commentPost', 'resolvedBy');

        if ($status !== null) {
            $queryBuilder
                ->andWhere('report.status = :status')
                ->setParameter('status', $status);
        }

        return $queryBuilder;
    }
}
