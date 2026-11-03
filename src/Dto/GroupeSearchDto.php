<?php

namespace App\Dto;

final class GroupeSearchDto
// final readonly class GroupeSearchDto
{
    public function __construct(
        public ?string $query = null,
        // public ?string $ville = null,
        // public ?string $nom = null,
    ) {}
}