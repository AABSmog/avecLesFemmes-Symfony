<?php

namespace App\Tests\Controller;

use App\Repository\GroupementRepository;
use App\Tests\SeedBase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class GroupementListeTest extends WebTestCase
{
    use SeedBase;

    public static function setUpBeforeClass(): void
    {
        self::seed();
    }

    public function testLaListeAfficheLesGroupementsEtLeurCompteur(): void
    {
        $client = static::createClient();
        $client->request('GET', '/groupement');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.sous-titre', '5 groupements suivis');
        self::assertStringContainsString('GIE Teranga Mbour', $client->getResponse()->getContent());
    }

    public function testLeFormulaireEstEnGetEtPorteLaValeurDuFiltre(): void
    {
        $client = static::createClient();
        $client->request('GET', '/groupement?query=mbour');
        $html = (string) $client->getResponse()->getContent();

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('method="get"', $html);
        self::assertStringContainsString('name="query"', $html);
        self::assertStringContainsString('value="mbour"', $html);
        self::assertStringNotContainsString('GIE Jappo Dakar', $html);
    }

    public function testLesTemplatesNUtilisentAucuneBaliseFormBrute(): void
    {
        $chemins = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(__DIR__ . '/../../templates', \FilesystemIterator::SKIP_DOTS),
        );

        foreach ($chemins as $fichier) {
            if ('twig' !== $fichier->getExtension()) {
                continue;
            }

            self::assertStringNotContainsString(
                '<form',
                (string) file_get_contents($fichier->getPathname()),
                sprintf('%s doit passer par un formulaire Symfony.', $fichier->getFilename()),
            );
        }
    }

    public function testChaqueLigneMeneALaFicheDesExercices(): void
    {
        $client = static::createClient();
        $client->request('GET', '/groupement');
        $html = (string) $client->getResponse()->getContent();

        self::assertStringContainsString('href="/' . $this->idGroupement('GIE Teranga Mbour') . '/exercice"', $html);
    }

    public function testLaFicheRenvoieALaListe(): void
    {
        $client = static::createClient();
        $client->request('GET', '/' . $this->idGroupement('GIE Teranga Mbour') . '/exercice');
        $html = (string) $client->getResponse()->getContent();

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('href="/groupement"', $html);
        self::assertStringContainsString('Encore 41 jours pour prêter', $html);
    }

    public function testUnGroupementSansExerciceAfficheLetatVide(): void
    {
        $client = static::createClient();
        $client->request('GET', '/' . $this->idGroupement('GIE Wàllar Thiès') . '/exercice');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.vide h2', 'Aucun exercice pour l\'instant');
        self::assertStringContainsString('GIE Wàllar Thiès', $client->getResponse()->getContent());
    }

    public function testUnGroupementInconnuRenvoieUn404(): void
    {
        $client = static::createClient();
        $client->request('GET', '/9999/exercice');

        self::assertResponseStatusCodeSame(404);
    }

    public function testLeMenuSignaleLaPageCourante(): void
    {
        $client = static::createClient();
        $client->request('GET', '/' . $this->idGroupement('GIE Teranga Mbour') . '/exercice');
        $html = (string) $client->getResponse()->getContent();

        self::assertStringContainsString('Exercices</a>', $html);
        self::assertMatchesRegularExpression('/aria-current="page"[^>]*>Exercices/', $html);
    }

    private function idGroupement(string $nom): int
    {
        $groupement = static::getContainer()->get(GroupementRepository::class)->findOneBy(['nom' => $nom]);
        self::assertNotNull($groupement, sprintf('Le groupement « %s » manque aux fixtures.', $nom));

        return (int) $groupement->getId();
    }
}
