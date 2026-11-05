<?php

namespace App\Tests\Controller;

use App\Tests\SeedBase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class NavigationTest extends WebTestCase
{
    use SeedBase;

    public static function setUpBeforeClass(): void
    {
        self::seed();
    }

    public function testLAccueilEstLePointEntree(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $html = (string) $client->getResponse()->getContent();

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('5 groupements suivis', $html);
        self::assertStringContainsString('href="/groupement"', $html);
        self::assertMatchesRegularExpression('/aria-current="page"[^>]*>Accueil/', $html);
    }

    public function testLesChiffresDeLAccueilViennentDeLaBase(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $html = (string) $client->getResponse()->getContent();

        self::assertStringContainsString('3 exercices en cours', $html);
    }

    public function testLeMenuSignaleLesGroupementsDepuisLaListe(): void
    {
        $client = static::createClient();
        $client->request('GET', '/groupement');

        self::assertMatchesRegularExpression('/aria-current="page"[^>]*>Groupements/', $client->getResponse()->getContent());
    }

    public function testLeFilDesPagesVientALAccueil(): void
    {
        $client = static::createClient();
        $client->request('GET', '/groupement');
        $html = (string) $client->getResponse()->getContent();

        self::assertStringContainsString('class="marque" href="/"', $html);
    }

    public function testAucunLienMortDansLesTemplates(): void
    {
        $chemins = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(__DIR__ . '/../../templates', \FilesystemIterator::SKIP_DOTS),
        );

        foreach ($chemins as $fichier) {
            if ('twig' !== $fichier->getExtension()) {
                continue;
            }

            self::assertStringNotContainsString(
                'href="#"',
                (string) file_get_contents($fichier->getPathname()),
                sprintf('%s ne doit pas garder de lien sans destination.', $fichier->getFilename()),
            );
        }
    }

    public function testLesFonctionsNonLivreesSontDesBoutonsDesactives(): void
    {
        $client = static::createClient();
        $client->request('GET', '/groupement');
        $html = (string) $client->getResponse()->getContent();

        self::assertStringContainsString('Nouveau groupement', $html);
        self::assertMatchesRegularExpression('/<button[^>]*disabled[^>]*>Nouveau groupement/', $html);
    }
}
