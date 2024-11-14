<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ExchangeController extends AbstractController
{
    #[Route('/exchange', name: 'app_exchange')]
    public function index(): Response
    {
        return $this->render('exchange/index.html.twig', [
        ]);
    }
}
