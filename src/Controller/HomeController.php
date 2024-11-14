<?php

namespace App\Controller;

use App\Entity\Potentiel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    #[Route('/home/{id}', name: 'app_home', methods: ['GET'])]

    public function index(Potentiel $potentiel): Response
    {
        // dd($potentiel);
        return $this->render('home/index.html.twig', [
            'potentiel' => $potentiel,
        ]);
    }

   /* public function show(): Response
    {
        return $this->render('potentiel/show.html.twig', [
            'potentiel' => $potentiel,
        ]);
    }*/
}
