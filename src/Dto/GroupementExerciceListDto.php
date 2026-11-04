<?php

namespace App\Dto;

use App\Entity\Groupement;

final readonly class GroupementExerciceListDto
{
    /**
     * @param ExerciceDto[] $exercices historique, du plus récent au plus ancien
     * @param EtapeFriseDto[] $etapes frise de l'exercice en cours, vide si aucun exercice
     */
    public function __construct(
        public int $id,
        public string $nom,
        public string $tel,
        public ?string $adresse,
        public array $exercices,
        public ?ExerciceDto $exerciceEncours,
        public array $etapes,
    ) {
    }

    public static function fromEntity(
        Groupement $groupementEntity,
        array $exercices,
        ?ExerciceDto $exerciceEncours,
        array $etapes,
    ): self {
        return new self(
            $groupementEntity->getId(),
            $groupementEntity->getNom(),
            $groupementEntity->getTel(),
            $groupementEntity->getAdresse(),
            $exercices,
            $exerciceEncours,
            $etapes,
        );
    }

    public function aDesExercices(): bool
    {
        return [] !== $this->exercices;
    }
}
