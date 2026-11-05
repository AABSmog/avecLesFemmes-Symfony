<?php

namespace App\Repository;

use App\Entity\Groupement;
use App\Enum\StatutExercice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use App\Dto\GroupeSearchDto;

/**
 * @extends ServiceEntityRepository<Groupement>
 */
class GroupementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Groupement::class);
    }

    public function save(Groupement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Groupement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllGroupementsAndExercicesEncours(GroupeSearchDto $searchDto): array
    {
        return $this->appliquerFiltre($this->listeQueryBuilder(), $searchDto)
            ->getQuery()
            ->getResult();
    }

    public function compterGroupements(): int
    {
        return (int) $this->createQueryBuilder('g')
            ->select('COUNT(g.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function listeQueryBuilder(): QueryBuilder
    {
        // La condition est dans le WITH : un where sur e.statut transformerait la
        // jointure en INNER JOIN et masquerait les groupements sans exercice en cours.
        return $this->createQueryBuilder('g')
            ->leftJoin('g.exercices', 'e', 'WITH', 'e.statut = :statut')
            ->addSelect('e')
            ->setParameter('statut', StatutExercice::ENCOURS)
            ->orderBy('g.nom', 'ASC')
            ->addOrderBy('g.id', 'ASC');
    }

    private function appliquerFiltre(QueryBuilder $qb, GroupeSearchDto $searchDto): QueryBuilder
    {
        if ($searchDto->aUneRecherche()) {
            $qb->andWhere('LOWER(g.nom) LIKE :query OR LOWER(g.adresse) LIKE :query')
                ->setParameter('query', $searchDto->recherchePourLike());
        }

        return $qb;
    }

    
//    /**
//     * @return Groupement[] Returns an array of Groupement objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('g.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Groupement
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
