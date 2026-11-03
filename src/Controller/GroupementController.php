<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\GroupementRepository;
use App\Service\GroupementExerciceService;
use App\Dto\GroupeSearchDto;
use App\Form\GroupeSearchType;

final class GroupementController extends AbstractController
{
    public function __construct(private readonly GroupementExerciceService $groupementExerciceService) {
       
    }
    
    // doit permettre l'affichage et le filtre
    #[Route('/groupement', name: 'app_groupement')]
    public function index(Request $request): Response
    {
        $searchDto = new GroupeSearchDto();
        // $searchDto->query = "TEST";
        $form = $this->createForm(GroupeSearchType::class, $searchDto);
        $form->handleRequest($request);
        $groupements = $this->groupementExerciceService->findAllGroupementsAndExercicesEncours($searchDto);
        
        return $this->render('groupement/index.html.twig', [
            'controller_name' => 'GroupementController',
            'dataGroupements' => $groupements,
            'formSearchGroupement' => $form->createView(),
        ]);
    }
}
