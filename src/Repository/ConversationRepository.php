<?php

namespace App\Repository;

use App\Entity\Conversation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Conversation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Conversation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Conversation[]    findAll()
 * @method Conversation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    public function findByParticipation($user)
    {
        return $this->createQueryBuilder('c')
            ->orWhere('c.user1 = :user1')
            ->orWhere('c.user2 = :user2')
            ->setParameters([
                'user1' => $user,
                'user2' => $user,
            ])
            ->orderBy('c.created', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOneByParticipation($user1)
    {
        return $this->createQueryBuilder('c')
            ->orWhere('c.user1 = :user1')
            ->orWhere('c.user2 = :user2')
            ->setParameters([
                'user1' => $user1,
                'user2' => $user1,
            ])
            ->orderBy('c.created', 'DESC')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findByParticipationNonLu($user)
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.lastMessage', 'l')
            ->andWhere('l.destinataire = :destinataire')
            ->andWhere('l.lu = :lu')
            ->setParameters([
                'destinataire' => $user,
                'lu' => false
            ])
            ->orderBy('c.created', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return Conversation[] Returns an array of Conversation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Conversation
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
