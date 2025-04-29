<?php

namespace App\Repository;

use App\Entity\SearchUser;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    /**
     * @var PaginatorInterface
     */
    private $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, User::class);

        $this->paginator = $paginator;
    }

    /**
     * Recupère les annonces en lien avec une recherche
     * @return PaginationInterface
     */
    public function findSearch(SearchUser $search): PaginationInterface
    {
        $query = $this->getSearcheQuery($search)->getQuery();

        return $this->paginator->paginate(
            $query,
            $search->page,
            16
        );
    }

    public function searchSeller(string $term): array
    {
        $qb = $this->createQueryBuilder('u');

        return $qb->where('u.compte = :seller')
            ->andWhere('u.ville LIKE :city')
            ->setParameters([
                'seller' => 'Vendeur',
                'city' => '%'.$term.'%'
            ])
            ->getQuery()
            ->getResult();
    }

    /**
     * //@return QueryBuilder
     */
    private function getSearcheQuery(SearchUser $search) //: QueryBuilder
    {

        $query = $this->createQueryBuilder('u')
            ->select('c', 'u')
            ->leftJoin('u.categorie', 'c')
            ->orderBy('u.avatar', 'DESC')
            ->addOrderBy('u.apropos', 'DESC')
            ;

        if (!empty($search->getName())) {
            $query = $query
                ->andWhere('u.nom LIKE :nom')
                ->orWhere('u.prenom LIKE :prenom')
                ->setParameters([
                    'nom' => "%{$search->getName()}%",
                    'prenom' => "%{$search->getName()}%"
                ]);
        }

        if (!empty($search->getEmail())) {
            $query = $query
                ->andWhere('u.email LIKE :email')
                ->setParameter('email', "%{$search->getEmail()}%");
        }

        if (!empty($search->getGenre())) {
            $query = $query
                ->andWhere('u.genre LIKE :genre')
                ->setParameter('genre', "%{$search->getGenre()}%");
        }

        if (!empty($search->getCompte())) {
            $query = $query
                ->andWhere('u.compte LIKE :compte')
                ->setParameter('compte', "%{$search->getCompte()}%");
        }

        if (!empty($search->getVille())) {
            $query = $query
                ->andWhere('u.ville LIKE :ville')
                ->setParameter('ville', "%{$search->getVille()}%");
        }

        if ($search->getCategories()->count() > 0) {
            $query = $query
                ->andWhere('c.id IN (:cat)')
                ->setParameter('cat', $search->categories);
        }

        if (!empty($search->getIsVerified())) {
            $query = $query
                ->andWhere('u.isVerified = :isVerified')
                ->setParameter('isVerified', $search->getIsVerified());
        }

        return $query;
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * Used to find user by role
     */
    public function findByRole(string $role)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.roles LIKE :role')
            ->setParameter('role', "%\"$role\"%")
            ->orderBy('u.created', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Used to find user by role
     */
    public function findByVille(string $ville)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.ville LIKE :ville')
            ->andWhere('u.compte = :compte')
            ->setParameters([
                'ville' => "%{$ville}%",
                'compte' => 'Vendeur'
            ])
            ->orderBy('u.created', 'DESC')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();
    }

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @return User[]
     */
    public function getNewlyRegistred(): array
    {
        $delaysMinus = new \DateTime('-15 minutes');
        $delaysPlus = new \DateTime('now');

        $qb = $this->createQueryBuilder('u');

        return $qb->where('u.created <= :delaysPlus')
            ->andWhere('u.created >= :delaysMinus')
            ->andWhere('u.isVerified = :isVerified')
            ->setParameters([
                'delaysPlus' => $delaysPlus,
                'delaysMinus' => $delaysMinus,
                'isVerified' => true,
            ])
            ->getQuery()
            ->getResult();
    }

    /**
     * @return User[]
     */
    public function getCriteriaValorisations(): array
    {
        $delaysMinus = new \DateTime('-5 minutes');
        $delaysPlus = new \DateTime('+5 minutes');

        $qb = $this->createQueryBuilder('u');

        return $qb->where('u.created <= :delaysPlus')
            ->andWhere('u.created >= :delaysMinus')
            ->andWhere('u.isVerified = :isVerified')
            ->setParameters([
                'delaysPlus' => $delaysPlus,
                'delaysMinus' => $delaysMinus,
                'isVerified' => true,
            ])
            ->getQuery()
            ->getResult();
    }

    /**
     * @return User[]
     */
    public function getUserNotHaveService(): array
    {
        $qb = $this->createQueryBuilder('u');

        $threeDays = new \DateTime('-3 days');

        return $qb
            ->leftJoin('u.microservices', 'ms')
            ->where('ms.id IS NULL')
            ->andWhere('u.compte = :vendeur')
            ->andWhere('u.isVerified = :isVerified')
            ->andWhere('u.created <= :threeDays')
            ->setParameters([
                'vendeur' => 'Vendeur',
                'isVerified' => true,
                'threeDays' => $threeDays,
            ])
            ->getQuery()
            ->getResult();
    }

    public function findByLocation($userAdresse): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.adresse = :adresse')
            ->andWhere('u.compte = :compte')
            ->setParameters([
                'adresse' => $userAdresse,
                'compte' => 'vendeur'
            ])
            ->getQuery()
            ->getResult();
    }
}
