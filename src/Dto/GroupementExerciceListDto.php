<?php

namespace App\Dto;

use App\Entity\Groupement;

final readonly class GroupementExerciceListDto
{
    // TODO : CONVERTI ENTITY gROUPEMENT EXERCICE EN DTO
    public function __construct(
        public int $id,
        public string $nom,
        public string $tel,
        public ?string $adresse,
        public array $exercices,
    ) {
    }

    public static function fromEntity(Groupement $groupementEntity, array $exercices): self
    {
        return new self(
            $groupementEntity->getId(),
            $groupementEntity->getNom(),
            $groupementEntity->getTel(),
            $groupementEntity->getAdresse(),
            $exercices,
        );
    }
}