<?php

namespace App\Dto;

use App\Entity\Groupement;

final readonly class GroupementListExerciceDto
{
    public function __construct(
        public int $id,
        public string $nom,
        public string $tel,
        public ?string $adresse,
        public ?ExerciceEncoursDto $exerciceEncoursDto,
        public int $nombreExercices,
    ) {
    }

    public static function fromEntity(
        Groupement $groupementEntity,
        ?ExerciceEncoursDto $exerciceEncoursDto,
        int $nombreExercices,
    ): self {
        return new self(
            $groupementEntity->getId(),
            $groupementEntity->getNom(),
            $groupementEntity->getTel(),
            $groupementEntity->getAdresse(),
            $exerciceEncoursDto,
            $nombreExercices,
        );
    }
}
