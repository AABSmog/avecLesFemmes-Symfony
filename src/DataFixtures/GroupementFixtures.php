<?php

namespace App\DataFixtures;

use App\Entity\Groupement;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class GroupementFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $data = [
            ['GIE Teranga Mbour', '+221 77 123 45 67', 'Mbour, Thiès'],
            ['GIE Jappo Dakar', '+221 78 234 56 78', 'Pikine, Dakar'],
            ['GIE Dëgg Saint-Louis', '+221 76 345 67 89', 'Sor, Saint-Louis'],
            ['GIE Wàllar Thiès', '+221 77 456 78 90', 'Thiès, Thiès'],
            ['GIE Ndingël Louga', '+221 70 567 89 01', 'Louga, Louga'],
        ];
        foreach ($data as $key => [$nom, $tel, $adresse]) {
            // $nom = $groupement[0];
            // $tel = $groupement[1];
            // $adresse = $groupement[2];
            $groupement = (new Groupement())
                        ->setNom($nom)
                        ->setTel($tel)
                        ->setAdresse($adresse);
            $manager->persist($groupement);
            // $manager->flush();
        }
        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
}
