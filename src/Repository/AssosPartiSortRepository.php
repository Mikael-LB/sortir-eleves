<?php

namespace App\Repository;

use App\Entity\AssosPartiSort;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AssosPartiSort|null find($id, $lockMode = null, $lockVersion = null)
 * @method AssosPartiSort|null findOneBy(array $criteria, array $orderBy = null)
 * @method AssosPartiSort[]    findAll()
 * @method AssosPartiSort[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AssosPartiSortRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AssosPartiSort::class);
    }

    // /**
    //  * @return AssosPartiSort[] Returns an array of AssosPartiSort objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AssosPartiSort
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
