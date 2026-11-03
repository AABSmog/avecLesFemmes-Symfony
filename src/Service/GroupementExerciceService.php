<?php

namespace App\Service;

use App\Dto\ExerciceEncoursDto;
use App\Dto\GroupementListExerciceDto;
use App\Dto\GroupementExerciceListDto;
use App\Dto\ExerciceDto;
use App\Repository\GroupementRepository;
use App\Repository\ExerciceRepository;
use App\Enum\StatutExercice;
use App\Dto\GroupeSearchDto;

class GroupementExerciceService
{
    public function __construct(
        private readonly GroupementRepository $groupementRepository,
        private readonly ExerciceRepository $exerciceRepository
    ) {
    }

    // TODO : find all groupement and exercices encours and convert them to GroupementListExerciceDto
    public function findAllGroupementsAndExercicesEncours(GroupeSearchDto $searchDto): array
    {
        // TODO : find all groupement and exercices encours and convert them to GroupementListExerciceDto
        //$groupements = $this->groupementRepository->findAll();
        $groupements = $this->groupementRepository->findAllGroupementsAndExercicesEncours($searchDto);
        
        // MAPPING : convert groupement and exercices encours to GroupementListExerciceDto
        return array_map(function ($groupement) {
            // $exerciceEncours = $groupement->getExercices()->filter(function ($exercice) {
            //     return $exercice->getStatut() === StatutExercice::ENCOURS;
            // })->first();
            $exerciceEncours = $groupement->getExercices()[0] ?? null;
            if ($exerciceEncours) {
                $exerciceEncoursDto = ExerciceEncoursDto::fromEntity($exerciceEncours);
                return GroupementListExerciceDto::fromEntity($groupement, $exerciceEncoursDto);
            }

            return null;
        }, $groupements);
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
}
