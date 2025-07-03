<?php

namespace App\Repository;

use App\Entity\Commande;
use App\Entity\User;
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

    // Ici nous allons recupèrer la commande qui est en cours.
    public function getCommandeApprocheDelails(): array
    {
        $qb = $this->createQueryBuilder('c');

        $qb->where('c.payed = :payed')
            ->andWhere('c.deliver =  :deliver')
            ->setParameters([
                'payed' => true,
                'deliver' => false,
            ]);

        return $qb->getQuery()->getResult();
    }

//    // Ici nous allons recupèrer la commande qui est en cours pour un client.
    public function getCommandeClientEncours(): array
    {
        $qb = $this->createQueryBuilder('c');

        $qb->where('c.payed = :payed')
            ->andWhere('c.deliver =  :payed')
            ->andWhere('c.cloturer =  :cloturer')
            ->setParameters([
                'payed' => true,
                'cloturer' => false,
            ]);

        return $qb->getQuery()->getResult();
    }

    public function getCommandePayer(): array
    {
        $qb = $this->createQueryBuilder('c');

        $qb->where('c.payed = :payed')
            ->andWhere('c.payed =  :payed')
            ->andWhere('c.cancel <>  :cancel')
            ->andWhere('c.deliver =  :cloturer')
            ->andWhere('c.cloturer =  :cloturer')
            ->setParameters([
                'payed' => true,
                'cloturer' => false,
                'cancel' => true,
            ]);

        return $qb->getQuery()->getResult();
    }


    public function lastCommandes(?User $client = null, ?User $vendeur = null): array
    {
        $qb = $this->createQueryBuilder('c');

        $qb->where('c.payed = :payed')
            ->setParameter('payed', true)
            ->orderBy(
                'CASE WHEN c.updated IS NOT NULL THEN c.updated ELSE c.created END',
                'DESC'
            );

        if ($client) {
            $qb->andWhere('c.client = :client')
                ->setParameter('client', $client);
        }

        if ($vendeur) {
            $qb->andWhere('c.vendeur = :vendeur')
                ->setParameter('vendeur', $vendeur);
        }

        return $qb->getQuery()->getResult();
    }

}
