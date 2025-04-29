<?php

namespace App\Repository;

use App\Entity\Portefeuille;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Portefeuille|null find($id, $lockMode = null, $lockVersion = null)
 * @method Portefeuille|null findOneBy(array $criteria, array $orderBy = null)
 * @method Portefeuille[]    findAll()
 * @method Portefeuille[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PortefeuilleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Portefeuille::class);
    }

    public function findTotalSoldeDisponioble()
    {
        return $this->createQueryBuilder('p')
            ->select("SUM(p.soldeDisponible) as solde")
            //->andWhere('p.statut = :statut')
            //->setParameter('statut', 'En attente')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findTotalSoldeEnCours()
    {
        return $this->createQueryBuilder('p')
            ->select("SUM(p.soldeEnCours) as solde")
            //->andWhere('p.statut = :statut')
            //->setParameter('statut', 'En attente')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    // /**
    //  * @return Portefeuille[] Returns an array of Portefeuille objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Portefeuille
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
