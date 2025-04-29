<?php

namespace App\Repository;

use App\Entity\ServiceOption;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ServiceOption>
 *
 * @method ServiceOption|null find($id, $lockMode = null, $lockVersion = null)
 * @method ServiceOption|null findOneBy(array $criteria, array $orderBy = null)
 * @method ServiceOption[]    findAll()
 * @method ServiceOption[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ServiceOptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ServiceOption::class);
    }

    public function save(ServiceOption $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ServiceOption $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return ServiceOption[] Returns an array of ServiceOption objects
     */
    public function findByService($service): array
    {
        return $this->createQueryBuilder('s')
            ->select('m', 's')
            ->join('s.microservice', 'm')
            ->andWhere('m = :microservice')
            ->setParameter('microservice', $service)
            ->orderBy('s.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    //    public function findOneBySomeField($value): ?ServiceOption
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
