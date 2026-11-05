<?php

namespace App\Service;

use App\Dto\AccueilDto;
use App\Dto\ChiffreDto;
use App\Dto\ExerciceEncoursDto;
use App\Dto\EtapeFriseDto;
use App\Dto\GroupeListeDto;
use App\Dto\GroupeSearchDto;
use App\Dto\GroupementExerciceListDto;
use App\Dto\GroupementListExerciceDto;
use App\Entity\Exercice;
use App\Entity\Groupement;
use App\Repository\ExerciceRepository;
use App\Repository\GroupementRepository;
use App\Dto\ExerciceDto;

class GroupementExerciceService
{
    public function __construct(
        private readonly GroupementRepository $groupementRepository,
        private readonly ExerciceRepository $exerciceRepository
    ) {
    }

    /**
     * Une seule méthode sert l'affichage et le filtre : le tri et la sélection
     * des exercices en cours sont faits par la requête du repository.
     */
    public function findAllGroupementsAndExercicesEncours(GroupeSearchDto $searchDto): GroupeListeDto
    {
        $groupements = $this->groupementRepository->findAllGroupementsAndExercicesEncours($searchDto);
        $nombres = $this->exerciceRepository->compterParGroupements(
            array_map(static fn (Groupement $groupement): int => (int) $groupement->getId(), $groupements)
        );

        $lignes = array_map(
            static function (Groupement $groupement) use ($nombres): GroupementListExerciceDto {
                // La jointure ne ramène que l'exercice en cours : premier élément = exercice en cours.
                $exerciceEncours = $groupement->getExercices()->first();

                return GroupementListExerciceDto::fromEntity(
                    $groupement,
                    $exerciceEncours instanceof Exercice ? ExerciceEncoursDto::fromEntity($exerciceEncours) : null,
                    $nombres[$groupement->getId()] ?? 0,
                );
            },
            $groupements,
        );

        return new GroupeListeDto(
            $lignes,
            $this->entete(count($lignes), $searchDto),
            $this->messageVide($searchDto),
            $searchDto->aUneRecherche(),
        );
    }

    public function findOneGroupementWithExercices(int $idGroupement): ?GroupementExerciceListDto
    {
        $groupement = $this->groupementRepository->find($idGroupement);
        if (!$groupement) {
            return null;
        }
        $exercices = $this->exerciceRepository->findBy(['groupement' => $idGroupement], ['statut' => 'DESC']);
        $exercicesDto = array_map(
            fn (Exercice $exercice): ExerciceDto => $this->exerciceDto($exercice),
            $exercices,
        );

        $exerciceEncours = $this->exerciceRepository->findEncoursPourGroupement($groupement);
        $encoursDto = $exerciceEncours instanceof Exercice ? $this->exerciceDto($exerciceEncours) : null;

        return GroupementExerciceListDto::fromEntity(
            $groupement,
            $exercicesDto,
            $encoursDto,
            $encoursDto instanceof ExerciceDto ? $this->etapesDeFrise($encoursDto) : [],
        );
    }

    private function exerciceDto(Exercice $exercice): ExerciceDto
    {
        return ExerciceDto::fromEntity(
            $exercice,
            $this->joursRestants($exercice->getLimitePretsAt()),
        );
    }

    /**
     * Nombre de jours entiers avant la date : négatif si la date est passée.
     */
    private function joursRestants(?\DateTimeInterface $date): ?int
    {
        if (!$date instanceof \DateTimeInterface) {
            return null;
        }

        $aujourdhui = new \DateTimeImmutable('today');
        $cible = \DateTimeImmutable::createFromInterface($date)->setTime(0, 0);

        return (int) $aujourdhui->diff($cible)->format('%r%a');
    }

    /**
     * Frise : étapes déjà passées, puis la prochaine, puis les suivantes.
     *
     * @return EtapeFriseDto[]
     */
    private function etapesDeFrise(ExerciceDto $encours): array
    {
        $aujourdhui = new \DateTimeImmutable('today');
        $etapesBrutes = [
            ['Début', $encours->dateDebut, null],
            ['Date limite des prêts', $encours->dateLimitePrets, $encours->notePrets()],
            ['Fin', $encours->dateFin, null],
            ['Rétribution', $encours->dateRetribution, null],
        ];

        $prochaineDejaMarquee = false;
        $etapes = [];

        foreach ($etapesBrutes as [$nom, $date, $note]) {
            $estPasse = $date instanceof \DateTimeInterface
                && \DateTimeImmutable::createFromInterface($date)->setTime(0, 0) < $aujourdhui;

            $classe = match (true) {
                $estPasse => 'etape etape--passee',
                !$prochaineDejaMarquee => 'etape etape--prochaine',
                default => 'etape',
            };

            if (!$estPasse) {
                $prochaineDejaMarquee = true;
            }

            $etapes[] = new EtapeFriseDto($nom, $date, $classe, $note);
        }

        return $etapes;
    }

    /**
     * Chiffres clés de la page d'accueil, comptés en base.
     */
    public function chiffresPourAccueil(): AccueilDto
    {
        $groupements = $this->groupementRepository->compterGroupements();
        $encours = $this->exerciceRepository->compterEncours();

        return new AccueilDto([
            new ChiffreDto($groupements, sprintf('%d groupement%s suivi%s', $groupements, $this->accord($groupements), $this->accord($groupements))),
            new ChiffreDto($encours, sprintf('%d exercice%s en cours', $encours, $this->accord($encours))),
        ]);
    }

    private function accord(int $total): string
    {
        return $total > 1 ? 's' : '';
    }

    private function entete(int $total, GroupeSearchDto $searchDto): string
    {
        $pluriel = $this->accord($total);

        if ($searchDto->aUneRecherche()) {
            return sprintf('%d groupement%s trouvé%s pour « %s »',
                $total, $pluriel, $pluriel, $searchDto->query);
        }

        return sprintf('%d groupement%s suivi%s', $total, $pluriel, $pluriel);
    }

    private function messageVide(GroupeSearchDto $searchDto): string
    {
        if (!$searchDto->aUneRecherche()) {
            return 'Aucun groupement suivi pour le moment.';
        }

        return sprintf('Aucun groupement ne correspond à « %s ».', $searchDto->query);
    }
}
