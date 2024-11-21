<?php

namespace App\Controller;

use App\Service\MailerService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }
    #[Route('/send-email', name: 'send_email')]
    public function sendEmail(MailerService $mailerService): JsonResponse
    {
        $url = 'https://example.com/confirm';
        $nom = 'John Doe';
        $email = 'issamanel05@gmail.com';

        $response = $mailerService->sendMail($url, $nom, $email);

        return new JsonResponse($response);
    }
}

