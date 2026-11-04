<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * Prépare la base de test une fois par classe : schéma complet puis fixtures de référence.
 */
trait SeedBase
{
    public static function seed(): void
    {
        $kernel = static::createKernel();
        $kernel->boot();

        $application = new Application($kernel);
        $application->setAutoExit(false);

        $commandes = [
            ['command' => 'doctrine:schema:update', '--force' => true, '--complete' => true, '--no-interaction' => true],
            ['command' => 'doctrine:fixtures:load', '--no-interaction' => true],
        ];

        foreach ($commandes as $commande) {
            if (0 !== $application->run(new ArrayInput($commande), new NullOutput())) {
                throw new \RuntimeException(sprintf('La commande « %s » a échoué.', $commande['command']));
            }
        }

        $kernel->shutdown();
    }
}
