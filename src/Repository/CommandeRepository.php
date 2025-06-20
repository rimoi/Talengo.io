<?php

namespace App\Repository;

use App\Entity\Commande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Commande|null find($id, $lockMode = null, $lockVersion = null)
 * @method Commande|null findOneBy(array $criteria, array $orderBy = null)
 * @method Commande[]    findAll()
 * @method Commande[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
    }
    
    public function findWhereUserIsClientOrVendeur($user)
    {
        return $this->createQueryBuilder('c')
            ->where('c.payed = :payed')
            ->andWhere('c.client = :client OR c.vendeur = :vendeur')
            ->setParameters([
                'client' => $user,
                'vendeur' => $user,
                'payed' => true
            ])
            ->orderBy('c.created', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
