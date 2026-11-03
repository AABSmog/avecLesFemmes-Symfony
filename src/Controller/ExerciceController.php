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
    #[Route('{idGroupement}/exercice', name: 'app_exercice', requirements: ['idGroupement' => '\d+'])]
    public function index(int $idGroupement): Response
    {
        $groupementExerciceListDto = $this->groupementExerciceService->findOneGroupementWithExercices($idGroupement);
        if ($groupementExerciceListDto === null) {
            // Rediriger vers la page de l'exercice
            return $this->redirectToRoute('app_groupement');
        }
        
        if (count($groupementExerciceListDto->exercices) === 0) {
            // Rediriger vers la page de l'exercice
            return $this->redirectToRoute('app_nouveau_exercice');
        }
        return $this->render('exercice/index.html.twig', [
            'controller_name' => 'ExerciceController',
            'groupementExerciceListDto' => $groupementExerciceListDto,
        ]);
    }

    #[Route('/nouveau-exercice', name: 'app_nouveau_exercice')]
    public function nouveau(): Response
    {
        return $this->render('exercice/nouveau.html.twig', [
            'controller_name' => 'ExerciceController',
        ]);
    }
}
