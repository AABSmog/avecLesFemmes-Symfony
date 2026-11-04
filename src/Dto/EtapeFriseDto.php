<?php

namespace App\Dto;

final readonly class EtapeFriseDto
{
    public function __construct(
        public string $nom,
        public ?\DateTimeInterface $date,
        public string $classe,
        public ?string $note,
    ) {
    }
}
