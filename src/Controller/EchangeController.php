<?php

namespace App\Controller;

use App\Entity\Echange;
use App\Entity\Message;
use App\Entity\Potentiel;
use App\Entity\User;
use App\Form\EchangeType;
use App\Repository\UserRepository;
use App\Repository\EchangeRepository;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/echange')]
final class EchangeController extends AbstractController
{
    #[Route(name: 'app_echange_index', methods: ['GET'])]
    public function index(EchangeRepository $echangeRepository, UserRepository $userRepository): Response
    {
        //recupere toutes les echanges de l'utilisateur courant
        $echanges = $echangeRepository->findAll();
        $currentUser =  $userRepository->findOneBy(array('email' => $this->getUser()->getUserIdentifier()));
        $echangesPotentiels = [];
        foreach ($echanges as $value) {
            if ($value->getOwner()->getId() == $currentUser->getId() || $value->getPotentiel()->getUser()->getId() == $currentUser->getId())
                $echangesPotentiels[] = $value;
        }
        //   dd($result);
        return $this->render('echange/index.html.twig', [
            'echangesPotentiels' => $echangesPotentiels,
        ]);
    }

    #[Route('/potentiel/new/{id}', name: 'app_echange__potentiel_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Potentiel $potentiel, EntityManagerInterface $entityManager): Response
    {
        $echange = new Echange();
        $echange->setOwner($this->getUser())
            ->setPotentiel($potentiel);

        // dd($echange);
        $entityManager->persist($echange);
        $entityManager->flush();
        return $this->redirectToRoute('app_echange_index', [], Response::HTTP_SEE_OTHER);

        // return $this->render('echange/new.html.twig', [
        //     'echange' => $echange,
        // ]);
    }
    #[Route('/message', name: 'app_echange_message_show', methods: ['GET', 'POST'])]
    public function show_message(MessageRepository $messageRepository, Request $request, UserRepository $userRepository, EchangeRepository $echangeRepository): Response
    {
        $currentUser =  $userRepository->findOneBy(array('email' => $this->getUser()->getUserIdentifier()));
        $echange = new Echange();
        $echange = $echangeRepository->find($request->request->get('id'));

        $messages = $messageRepository->findBy(
            ['echange' => $echange->getId()],
            ['id' => 'ASC'],
            20

        );
        $result = [];
        $lastMessage="";
        foreach ($messages as $value) {
            $lastMessage = $value->getContenu();
            $result[] = array(
                'echange_id' => $request->request->get('id'),
                'contenu' => $value->getContenu(),
                'sender' => $value->getSender()->getId(),
                'recipient' => $value->getRecipient()->getId(),
                'currentUser' => $currentUser->getId(),


            );
        }
        $data = array(
            'messages' => $result,
            'lastMessage'=> $lastMessage,
            'potentiel' => array(
                'name' => $echange->getPotentiel()->getName(),
                'description' => $echange->getPotentiel()->getDescription(),
                'image' => 'img/' . $echange->getPotentiel()->getImg(),
                'user_id' => $echange->getPotentiel()->getUser()->getId()
            ),
            'owner' => array(
                'name' => $echange->getOwner()->getFirstName() . ' ' . $echange->getOwner()->getLastName(),
                'profile' => 'img/' . $echange->getOwner()->getProfile()
            )
        );

        return new Response(
            json_encode($data)
        );
    }


    #[Route('/message/send', name: 'app_echange_message_send', methods: ['GET', 'POST'])]
    public function send_message(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository, EchangeRepository $echangeRepository): Response
    {
        $currentUser =  $userRepository->findOneBy(array('email' => $this->getUser()->getUserIdentifier()));
        $echange = new Echange();
        $message = new Message();
        $sender = new User();
        $recipient = new User();
        $echange = $echangeRepository->find($request->request->get('echange_id'));

        if ($echange->getOwner()->getId() == $currentUser->getId()) {
            $sender = $this->getUser();
            $recipient = $echange->getPotentiel()->getUser();
        } else {
            $sender = $echange->getPotentiel()->getUser();
            $recipient =  $this->getUser();
        }
        $message->setEchange($echange)
            ->setContenu($request->request->get('message'))
            ->setSender($sender)
            ->setRecipient($recipient);


        $entityManager->persist($message);
        $entityManager->flush();


        return new Response(
            json_encode('successfully')
        );
    }


    
    #[Route('/{id}', name: 'app_echange_show', methods: ['GET'])]
    public function show(Echange $echange): Response
    {
        return $this->render('echange/show.html.twig', [
            'echange' => $echange,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_echange_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Echange $echange, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EchangeType::class, $echange);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_echange_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('echange/edit.html.twig', [
            'echange' => $echange,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_echange_delete', methods: ['POST'])]
    public function delete(Request $request, Echange $echange, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $echange->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($echange);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_echange_index', [], Response::HTTP_SEE_OTHER);
    }
}
