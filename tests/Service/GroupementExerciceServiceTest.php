<?php

namespace App\Tests\Service;

use App\Dto\GroupeSearchDto;
use App\Entity\Groupement;
use App\Repository\GroupementRepository;
use App\Service\GroupementExerciceService;
use App\Tests\SeedBase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class GroupementExerciceServiceTest extends KernelTestCase
{
    use SeedBase;

    private GroupementExerciceService $service;

    public static function setUpBeforeClass(): void
    {
        self::seed();
    }

    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel();
        $this->service = static::getContainer()->get(GroupementExerciceService::class);
    }

    public function testLaListeSansFiltreTrieeParNom(): void
    {
        $liste = $this->service->findAllGroupementsAndExercicesEncours(new GroupeSearchDto());

        self::assertCount(5, $liste->groupements);
        self::assertSame('5 groupements suivis', $liste->entete);
        self::assertFalse($liste->filtreActif);
        self::assertSame([
            'GIE Dëgg Saint-Louis',
            'GIE Jappo Dakar',
            'GIE Ndingël Louga',
            'GIE Teranga Mbour',
            'GIE Wàllar Thiès',
        ], array_map(static fn ($ligne): string => $ligne->nom, $liste->groupements));
    }

    public function testLeNombreExercicesVientDUneRequeteSql(): void
    {
        $liste = $this->service->findAllGroupementsAndExercicesEncours(new GroupeSearchDto());

        self::assertSame([1, 2, 1, 3, 0], array_map(
            static fn ($ligne): int => $ligne->nombreExercices,
            $liste->groupements,
        ));
    }

    public function testFiltreParNom(): void
    {
        $liste = $this->service->findAllGroupementsAndExercicesEncours(new GroupeSearchDto('Teranga'));

        self::assertCount(1, $liste->groupements);
        self::assertSame('GIE Teranga Mbour', $liste->groupements[0]->nom);
        self::assertTrue($liste->filtreActif);
        self::assertSame('1 groupement trouvé pour « Teranga »', $liste->entete);
    }

    public function testFiltreInsensibleALaCasse(): void
    {
        $liste = $this->service->findAllGroupementsAndExercicesEncours(new GroupeSearchDto('TERANGA'));

        self::assertCount(1, $liste->groupements);
    }

    public function testFiltreSurLaVilleDansLAdresse(): void
    {
        $liste = $this->service->findAllGroupementsAndExercicesEncours(new GroupeSearchDto('dakar'));

        self::assertSame(['GIE Jappo Dakar'], array_map(
            static fn ($ligne): string => $ligne->nom,
            $liste->groupements,
        ));
    }

    public function testFiltreSurUneVilleCommuneAuxDeuxGroupements(): void
    {
        $liste = $this->service->findAllGroupementsAndExercicesEncours(new GroupeSearchDto('Thiès'));

        self::assertCount(2, $liste->groupements);
        self::assertSame('2 groupements trouvés pour « Thiès »', $liste->entete);
    }

    public function testAucunResultatLaisseUnePlaceVideExplicite(): void
    {
        $liste = $this->service->findAllGroupementsAndExercicesEncours(new GroupeSearchDto('zzz'));

        self::assertSame([], $liste->groupements);
        self::assertStringContainsString('zzz', $liste->messageVide);
    }

    public function testUnGroupementSansExerciceEnCoursResteDansLaListe(): void
    {
        $liste = $this->service->findAllGroupementsAndExercicesEncours(new GroupeSearchDto('Jappo'));

        self::assertSame('GIE Jappo Dakar', $liste->groupements[0]->nom);
        self::assertNull($liste->groupements[0]->exerciceEncoursDto);
        self::assertSame(2, $liste->groupements[0]->nombreExercices);
    }

    public function testFicheCalculLesJoursRestantsCoteService(): void
    {
        $fiche = $this->ficheDe('GIE Teranga Mbour');

        self::assertTrue($fiche->aDesExercices());
        self::assertSame(41, $fiche->exerciceEncours?->joursRestants);
        self::assertSame('Encore 41 jours pour prêter', $fiche->exerciceEncours?->notePrets());
    }

    public function testLaFriseMarqueLesEtapesPasseesEtLaProchaine(): void
    {
        $fiche = $this->ficheDe('GIE Teranga Mbour');

        self::assertSame([
            'etape etape--passee',
            'etape etape--prochaine',
            'etape',
            'etape',
        ], array_map(static fn ($etape): string => $etape->classe, $fiche->etapes));
    }

    public function testFriseDUnExerciceDontLesPretsSontClos(): void
    {
        $fiche = $this->ficheDe('GIE Ndingël Louga');

        self::assertSame(-20, $fiche->exerciceEncours?->joursRestants);
        self::assertSame('Prêts clos depuis 20 jours', $fiche->exerciceEncours?->notePrets());
        self::assertSame([
            'etape etape--passee',
            'etape etape--passee',
            'etape etape--prochaine',
            'etape',
        ], array_map(static fn ($etape): string => $etape->classe, $fiche->etapes));
    }

    public function testFicheSansExerciceEnCours(): void
    {
        $fiche = $this->ficheDe('GIE Jappo Dakar');

        self::assertNull($fiche->exerciceEncours);
        self::assertSame([], $fiche->etapes);
        self::assertCount(2, $fiche->exercices);
    }

    public function testFicheDUnGroupementSansAucunExercice(): void
    {
        $fiche = $this->ficheDe('GIE Wàllar Thiès');

        self::assertFalse($fiche->aDesExercices());
        self::assertSame([], $fiche->exercices);
        self::assertSame([], $fiche->etapes);
    }

    public function testFicheDUnGroupementInconnu(): void
    {
        self::assertNull($this->service->findOneGroupementWithExercices(9999));
    }

    public function testLesChiffresDeLAccueilSontComptesEnBase(): void
    {
        $accueil = $this->service->chiffresPourAccueil();

        self::assertSame([5, 3], array_map(static fn ($chiffre): int => $chiffre->valeur, $accueil->chiffres));
        self::assertSame(['5 groupements suivis', '3 exercices en cours'], array_map(
            static fn ($chiffre): string => $chiffre->libelle,
            $accueil->chiffres,
        ));
    }

    private function ficheDe(string $nom): \App\Dto\GroupementExerciceListDto
    {
        return $this->service->findOneGroupementWithExercices($this->groupement($nom)->getId());
    }

    private function groupement(string $nom): Groupement
    {
        $groupement = static::getContainer()->get(GroupementRepository::class)
            ->findOneBy(['nom' => $nom]);

        self::assertInstanceOf(Groupement::class, $groupement, sprintf('Le groupement « %s » manque aux fixtures.', $nom));

        return $groupement;
    }
}
