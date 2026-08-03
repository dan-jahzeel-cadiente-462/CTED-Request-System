<?php

namespace App\Repository;

use App\Entity\Request;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
    public function findRequestsPage(int $page, int $limit): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.time_in', 'DESC')
            ->setFirstResult(max(0, ($page - 1) * $limit))
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
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
