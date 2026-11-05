<?php

namespace App\Dto;

final readonly class ChiffreDto
{
    public function __construct(
        public int $valeur,
        public string $libelle,
    ) {
    }
}
