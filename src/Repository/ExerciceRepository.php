<?php

namespace App\Repository;

use App\Entity\Exercice;
use App\Entity\Groupement;
use App\Enum\StatutExercice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Exercice>
 */
class ExerciceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Exercice::class);
    }

    /**
     * Nombre d'exercices par groupement, en une seule requête d'agrégation.
     *
     * @param int[] $idsGroupements
     * @return array<int, int> groupement id => nombre d'exercices
     */
    public function compterParGroupements(array $idsGroupements): array
    {
        if ([] === $idsGroupements) {
            return [];
        }

        $lignes = $this->createQueryBuilder('e')
            ->select('IDENTITY(e.groupement) AS idGroupement', 'COUNT(e.id) AS nombre')
            ->where('e.groupement IN (:ids)')
            ->setParameter('ids', $idsGroupements)
            ->groupBy('idGroupement')
            ->getQuery()
            ->getResult();

        return array_combine(
            array_column($lignes, 'idGroupement'),
            array_map(static fn (array $ligne): int => (int) $ligne['nombre'], $lignes),
        );
    }

    public function compterEncours(): int
    {
        return (int) $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->andWhere('e.statut = :statut')
            ->setParameter('statut', StatutExercice::ENCOURS)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Exercice en cours le plus récent d'un groupement, ou null.
     */
    public function findEncoursPourGroupement(Groupement $groupement): ?Exercice
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.groupement = :groupement')
            ->andWhere('e.statut = :statut')
            ->setParameter('groupement', $groupement)
            ->setParameter('statut', StatutExercice::ENCOURS)
            ->orderBy('e.debutAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

//    /**
//     * @return Exercice[] Returns an array of Exercice objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Exercice
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
