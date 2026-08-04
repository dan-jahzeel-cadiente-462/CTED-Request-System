<?php

namespace App\Repository;

use App\Entity\Request;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Request>
 */
class RequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Request::class);
    }

    /**
     * @return Request[]
     */
    public function findRequestsPage(int $page, int $limit, array $filters = []): array
    {
        $qb = $this->createFilterQueryBuilder($filters)
            ->orderBy('r.time_in', 'DESC')
            ->setFirstResult(max(0, ($page - 1) * $limit))
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    public function countRequests(array $filters = []): int
    {
        return (int) $this->createFilterQueryBuilder($filters)
            ->select('COUNT(r.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return Request[]
     */
    public function findRequests(array $filters = []): array
    {
        return $this->createFilterQueryBuilder($filters)
            ->orderBy('r.time_in', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findDistinctPrograms(): array
    {
        return array_column($this->createQueryBuilder('r')
            ->select('DISTINCT r.program')
            ->orderBy('r.program', 'ASC')
            ->getQuery()
            ->getArrayResult(), 'program');
    }

    public function findDistinctRequestTypes(): array
    {
        return array_column($this->createQueryBuilder('r')
            ->select('DISTINCT r.request_type')
            ->orderBy('r.request_type', 'ASC')
            ->getQuery()
            ->getArrayResult(), 'request_type');
    }

    public function findDistinctStatuses(): array
    {
        return array_column($this->createQueryBuilder('r')
            ->select('DISTINCT r.status')
            ->orderBy('r.status', 'ASC')
            ->getQuery()
            ->getArrayResult(), 'status');
    }

    private function createFilterQueryBuilder(array $filters): QueryBuilder
    {
        $qb = $this->createQueryBuilder('r');

        if (!empty($filters['program'])) {
            $qb->andWhere('r.program = :program')
                ->setParameter('program', $filters['program']);
        }

        if (!empty($filters['status'])) {
            $qb->andWhere('r.status = :status')
                ->setParameter('status', $filters['status']);
        }

        if (!empty($filters['requestType'])) {
            $qb->andWhere('r.request_type = :requestType')
                ->setParameter('requestType', $filters['requestType']);
        }

        if (!empty($filters['studentId'])) {
            $qb->andWhere('r.student_id LIKE :studentId')
                ->setParameter('studentId', '%' . $filters['studentId'] . '%');
        }

        if (!empty($filters['fullName'])) {
            $qb->andWhere('r.full_name LIKE :fullName')
                ->setParameter('fullName', '%' . $filters['fullName'] . '%');
        }

        return $qb;
    }

    /**
     * @return Request[]
     */
    public function findRequestsByDate(string $date): array
    {
        $dateTime = \DateTimeImmutable::createFromFormat('Y-m-d', $date);
        if ($dateTime === false) {
            return [];
        }

        $start = $dateTime->setTime(0, 0, 0);
        $end = $dateTime->setTime(23, 59, 59);

        return $this->createQueryBuilder('r')
            ->andWhere('r.time_in BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('r.time_in', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countByStatus(string $status): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->andWhere('r.status = :status')
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countStatuses(array $statuses): array
    {
        $results = $this->createQueryBuilder('r')
            ->select('r.status AS status, COUNT(r.id) AS count')
            ->andWhere('r.status IN (:statuses)')
            ->setParameter('statuses', $statuses)
            ->groupBy('r.status')
            ->getQuery()
            ->getArrayResult();

        return array_column($results, 'count', 'status');
    }

//    /**
//     * @return Request[] Returns an array of Request objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Request
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
