<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\GroupementRepository;
use App\Service\GroupementExerciceService;

final class GroupementController extends AbstractController
{
    public function __construct(private readonly GroupementExerciceService $groupementExerciceService) {
       
    }
    
    // doit permettre l'affichage et le filtre
    #[Route('/groupement', name: 'app_groupement')]
    public function index(): Response
    {
        $groupements = $this->groupementExerciceService->findAllGroupementsAndExercicesEncours();
        return $this->render('groupement/index.html.twig', [
            'controller_name' => 'GroupementController',
            'dataGroupements' => $groupements
        ]);
    }
}
