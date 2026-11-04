<?php

namespace App\Dto;

use App\Entity\Exercice;

final readonly class ExerciceDto
{
    public function __construct(
        public int $id,
        public \DateTime $dateDebut,
        public ?\DateTime $dateFin,
        public ?\DateTime $dateLimitePrets,
        public ?\DateTime $dateRetribution,
        public string $statut,
        public string $classeStatut,
        public ?int $joursRestants,
    ) {
    }

    /**
     * @param int|null $joursRestants calculé par le service : le DTO ne query pas la date du jour
     */
    public static function fromEntity(Exercice $exerciceEntity, ?int $joursRestants = null): self
    {
        return new self(
            $exerciceEntity->getId(),
            $exerciceEntity->getDebutAt(),
            $exerciceEntity->getFinAt(),
            $exerciceEntity->getLimitePretsAt(),
            $exerciceEntity->getRetributionAt(),
            $exerciceEntity->getStatut()->label(),
            $exerciceEntity->getStatut()->classeStatut(),
            $joursRestants,
        );
    }

    public function notePrets(): ?string
    {
        if (!$this->dateLimitePrets instanceof \DateTime || null === $this->joursRestants) {
            return null;
        }

        return match (true) {
            $this->joursRestants > 1 => sprintf('Encore %d jours pour prêter', $this->joursRestants),
            $this->joursRestants >= 0 => 'Dernier jour pour prêter',
            default => sprintf('Prêts clos depuis %d jour%s',
                -$this->joursRestants, -$this->joursRestants > 1 ? 's' : ''),
        };
    }
}
