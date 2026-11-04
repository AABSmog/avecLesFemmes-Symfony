<?php

namespace App\Tests\Dto;

use App\Dto\ExerciceDto;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ExerciceDtoTest extends TestCase
{
    public static function notePretsProvider(): iterable
    {
        yield 'dans plusieurs jours' => [41, 'Encore 41 jours pour prêter'];
        yield 'un jour restant' => [1, 'Dernier jour pour prêter'];
        yield 'date limite aujourd\'hui' => [0, 'Dernier jour pour prêter'];
        yield 'clos depuis un jour' => [-1, 'Prêts clos depuis 1 jour'];
        yield 'clos depuis vingt jours' => [-20, 'Prêts clos depuis 20 jours'];
    }

    #[DataProvider('notePretsProvider')]
    public function testLaNoteDePretsSuitLeNombreDeJours(?int $jours, string $attendu): void
    {
        self::assertSame($attendu, self::dto($jours, new \DateTime('2026-12-15'))->notePrets());
    }

    public function testNoteInexistanteSansDateLimite(): void
    {
        self::assertNull(self::dto(10, null)->notePrets());
    }

    public function testNoteInexistanteSansCalculDeJours(): void
    {
        self::assertNull(self::dto(null, new \DateTime('2026-12-15'))->notePrets());
    }

    private static function dto(?int $jours, ?\DateTime $limitePrets): ExerciceDto
    {
        return new ExerciceDto(
            1,
            new \DateTime('2026-03-01'),
            new \DateTime('2027-02-28'),
            $limitePrets,
            new \DateTime('2027-03-14'),
            'En cours',
            'statut--en_cours',
            $jours,
        );
    }
}
