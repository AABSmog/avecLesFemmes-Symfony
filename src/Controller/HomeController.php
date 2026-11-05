<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\GroupementExerciceService;

final class HomeController extends AbstractController
{
    public function __construct(private readonly GroupementExerciceService $groupementExerciceService)
    {
    }

    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'accueil' => $this->groupementExerciceService->chiffresPourAccueil(),
        ]);
    }
}
