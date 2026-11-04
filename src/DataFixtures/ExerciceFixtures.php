<?php

namespace App\DataFixtures;

use App\Entity\Exercice;
use App\Enum\StatutExercice;
use App\Repository\GroupementRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ExerciceFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private readonly GroupementRepository $repo) {
        
    }

    /**
     * Décalages en jours par rapport à aujourd'hui, pour que la démonstration
     * reste parlante quelle que soit la date de la soutenance.
     */
    private const CYCLES = [
        'GIE Teranga Mbour' => [
            ['debut' => -975, 'limite' => -695, 'fin' => -610, 'retribution' => -595, 'statut' => 'cloture'],
            ['debut' => -610, 'limite' => -330, 'fin' => -245, 'retribution' => -230, 'statut' => 'cloture'],
            ['debut' => -240, 'limite' => 41, 'fin' => 120, 'retribution' => 135, 'statut' => 'en_cours'],
        ],
        'GIE Jappo Dakar' => [
            ['debut' => -975, 'limite' => -695, 'fin' => -610, 'retribution' => -595, 'statut' => 'cloture'],
            ['debut' => -610, 'limite' => -330, 'fin' => -245, 'retribution' => -230, 'statut' => 'cloture'],
        ],
        'GIE Dëgg Saint-Louis' => [
            ['debut' => -240, 'limite' => 0, 'fin' => 120, 'retribution' => 135, 'statut' => 'en_cours'],
        ],
        'GIE Wàllar Thiès' => [],
        'GIE Ndingël Louga' => [
            ['debut' => -240, 'limite' => -20, 'fin' => 60, 'retribution' => 75, 'statut' => 'en_cours'],
        ],
    ];

    public function getDependencies(): array {
        return [GroupementFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        foreach ($this->repo->findAll() as $groupement) {
            foreach (self::CYCLES[$groupement->getNom()] ?? [] as $cycle) {
                $exercice = (new Exercice())
                    ->setGroupement($groupement)
                    ->setDebutAt(self::date($cycle['debut']))
                    ->setLimitePretsAt(self::date($cycle['limite']))
                    ->setFinAt(self::date($cycle['fin']))
                    ->setRetributionAt(self::date($cycle['retribution']))
                    ->setStatut(StatutExercice::from($cycle['statut']));
                $manager->persist($exercice);
            }
        }

        $manager->flush();
    }

    private static function date(int $decalageJours): \DateTime
    {
        return (new \DateTime('today'))->modify(sprintf('%+d days', $decalageJours));
    }
}
