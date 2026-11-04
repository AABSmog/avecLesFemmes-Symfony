<?php

namespace App\Dto;

final readonly class GroupeListeDto
{
    /**
     * @param GroupementListExerciceDto[] $groupements
     */
    public function __construct(
        public array $groupements,
        public string $entete,
        public string $messageVide,
        public bool $filtreActif,
    ) {
    }
}
