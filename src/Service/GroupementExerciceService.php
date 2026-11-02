<?php

namespace App\Service;
use App\Dto\ExerciceEncoursDto;
use App\Dto\GroupementListExerciceDto;
use App\Repository\GroupementRepository;
use App\Enum\StatutExercice;

class GroupementExerciceService
{
    public function __construct(private readonly GroupementRepository $groupementRepository)
    {
    }
    // TODO : find all groupement and exercices encours and convert them to GroupementListExerciceDto
    public function findAllGroupementsAndExercicesEncours(): array
    {
        // TODO : find all groupement and exercices encours and convert them to GroupementListExerciceDto
        //$groupements = $this->groupementRepository->findAll();
        $groupements = $this->groupementRepository->findAllGroupementsAndExercicesEncours();
        
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

}