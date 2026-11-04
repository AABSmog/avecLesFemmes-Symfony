<?php

namespace App\Dto;

final class GroupeSearchDto
{
    public function __construct(
        public ?string $query = null,
    ) {
    }

    public function setQuery(mixed $query): void
    {
        $this->query = \is_string($query) && '' !== trim($query) ? trim($query) : null;
    }

    public function aUneRecherche(): bool
    {
        return null !== $this->query;
    }

    public function recherchePourLike(): string
    {
        return '%' . mb_strtolower((string) $this->query) . '%';
    }
}
