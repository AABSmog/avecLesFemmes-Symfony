<?php

namespace App\Dto;

final readonly class AccueilDto
{
    /**
     * @param ChiffreDto[] $chiffres
     */
    public function __construct(
        public array $chiffres,
    ) {
    }
}
