<?php

namespace App\Repository;

use App\Entity\Suivis;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Suivis|null find($id, $lockMode = null, $lockVersion = null)
 * @method Suivis|null findOneBy(array $criteria, array $orderBy = null)
 * @method Suivis[]    findAll()
 * @method Suivis[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SuivisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Suivis::class);
    }

    // /**
    //  * @return Suivis[] Returns an array of Suivis objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Suivis
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
