<?php

namespace App\Enum;

enum StatutExercice: string {
    case ENCOURS = 'en_cours';
    case CLOTURE = 'cloture';

    public function label(): string {
        return match ($this) {
            self::ENCOURS => "En cours",
            self::CLOTURE => "Clôturé",
        };
    }

    public function classeStatut(): string {
        return 'statut--' . $this->value;
    }
}