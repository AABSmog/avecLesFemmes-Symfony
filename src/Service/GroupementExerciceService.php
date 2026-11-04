<?php

namespace App\Service;

use App\Dto\ExerciceEncoursDto;
use App\Dto\GroupeListeDto;
use App\Dto\GroupeSearchDto;
use App\Dto\GroupementExerciceListDto;
use App\Dto\GroupementListExerciceDto;
use App\Entity\Exercice;
use App\Entity\Groupement;
use App\Repository\ExerciceRepository;
use App\Repository\GroupementRepository;
use App\Dto\ExerciceDto;

class GroupementExerciceService
{
    public function __construct(
        private readonly GroupementRepository $groupementRepository,
        private readonly ExerciceRepository $exerciceRepository
    ) {
    }

    /**
     * Une seule méthode sert l'affichage et le filtre : le tri et la sélection
     * des exercices en cours sont faits par la requête du repository.
     */
    public function findAllGroupementsAndExercicesEncours(GroupeSearchDto $searchDto): GroupeListeDto
    {
        $groupements = $this->groupementRepository->findAllGroupementsAndExercicesEncours($searchDto);
        $nombres = $this->exerciceRepository->compterParGroupements(
            array_map(static fn (Groupement $groupement): int => (int) $groupement->getId(), $groupements)
        );

        $lignes = array_map(
            static function (Groupement $groupement) use ($nombres): GroupementListExerciceDto {
                // La jointure ne ramène que l'exercice en cours : premier élément = exercice en cours.
                $exerciceEncours = $groupement->getExercices()->first();

                return GroupementListExerciceDto::fromEntity(
                    $groupement,
                    $exerciceEncours instanceof Exercice ? ExerciceEncoursDto::fromEntity($exerciceEncours) : null,
                    $nombres[$groupement->getId()] ?? 0,
                );
            },
            $groupements,
        );

        return new GroupeListeDto(
            $lignes,
            $this->entete(count($lignes), $searchDto),
            $this->messageVide($searchDto),
            $searchDto->aUneRecherche(),
        );
    }

    public function findOneGroupementWithExercices(int $idGroupement): ?GroupementExerciceListDto
    {
        $groupement = $this->groupementRepository->find($idGroupement);
        if (!$groupement) {
            return null;
        }
        $exercices = $this->exerciceRepository->findBy(['groupement' => $idGroupement], ['statut' => 'DESC']);
        $exercicesDto = array_map(function ($exercice) {
            return ExerciceDto::fromEntity($exercice);
        }, $exercices);
        return GroupementExerciceListDto::fromEntity($groupement, $exercicesDto);
    }

    private function entete(int $total, GroupeSearchDto $searchDto): string
    {
        $pluriel = $total > 1 ? 's' : '';

        if ($searchDto->aUneRecherche()) {
            return sprintf('%d groupement%s trouvé%s pour « %s »',
                $total, $pluriel, $pluriel, $searchDto->query);
        }

        return sprintf('%d groupement%s suivi%s', $total, $pluriel, $pluriel);
    }

    private function messageVide(GroupeSearchDto $searchDto): string
    {
        if (!$searchDto->aUneRecherche()) {
            return 'Aucun groupement suivi pour le moment.';
        }

        return sprintf('Aucun groupement ne correspond à « %s ».', $searchDto->query);
    }
}
