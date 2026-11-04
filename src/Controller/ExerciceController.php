<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\GroupementExerciceService;

final class ExerciceController extends AbstractController
{
    public function __construct(private readonly GroupementExerciceService $groupementExerciceService) {
       
    }
    
    // \d+' : expression régulière pour indiquer que l'ID doit être un nombre entier
    // d : indique que l'ID doit être un chiffre (0-9)
    // + : indique que l'ID doit contenir au moins un chiffre
    #[Route('{idGroupement}/exercice', name: 'app_exercice', methods: ['GET'], requirements: ['idGroupement' => '\d+'])]
    public function index(int $idGroupement): Response
    {
        $groupementExerciceListDto = $this->groupementExerciceService->findOneGroupementWithExercices($idGroupement);

        if ($groupementExerciceListDto === null) {
            throw $this->createNotFoundException(sprintf('Le groupement « %d » n\'existe pas.', $idGroupement));
        }

        // Un groupement sans exercice a sa propre vue d'état vide, avec ses données à lui.
        $vue = $groupementExerciceListDto->aDesExercices()
            ? 'exercice/index.html.twig'
            : 'exercice/nouveau.html.twig';

        return $this->render($vue, [
            'groupementExerciceListDto' => $groupementExerciceListDto,
        ]);
    }
}
