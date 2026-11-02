<?php

namespace App\Dto;

use App\Entity\Exercice;

final readonly class ExerciceEncoursDto
{
    public function __construct(
        public int $id,
        public \DateTime $dateDebut,
        public \DateTime $dateFin,
        public string $statut,
    ) {
    }

    public static function fromEntity(Exercice $exerciceEntity): self
    {
        return new self(
            $exerciceEntity->getId(),
            $exerciceEntity->getDebutAt(),
            $exerciceEntity->getFinAt(),
            $exerciceEntity->getStatut()->label(),
        );
    }
}