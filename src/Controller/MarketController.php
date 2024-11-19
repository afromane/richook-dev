<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Besoin;
use App\Entity\Echange;
use App\Entity\Message;
use App\Repository\UserRepository;
use App\Repository\BesoinRepository;
use App\Repository\EchangeRepository;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/market')]
class MarketController extends AbstractController
{
    #[Route(name: 'app_market')]
    public function index(): Response
    {
        return $this->render('market/index.html.twig', [
            'controller_name' => 'MarketController',
        ]);
    }

    #[Route('/needs', name: 'app_market_needs')]
    public function needs(UserRepository $userRepository, BesoinRepository $besoinRepository): Response
    {
        // Retrieve all needs whose are public by exclude or the current user
        $currentUser = new User();
        $currentUser =  $userRepository->findOneBy(array('email' => $this->getUser()->getUserIdentifier()));
        $needs = [];
        foreach ($besoinRepository->findBy(array('isPrivate' => false)) as  $value) {
            if ($currentUser->getId() != $value->getUser()->getId()) {
                $needs[] = $value;
            }
        }

        return $this->render('market/needs.html.twig', [
            'needs' => $needs
        ]);
    }

    #[Route('/needs/exchange/start', name: 'app_market_needs_start_exchange', methods: ['GET', 'POST'])]
    public function new(Request $request, EchangeRepository $echangeRepository,MessageRepository $messageRepository, BesoinRepository $besoinRepository, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $besoin = new Besoin();
        $besoin = $besoinRepository->find($request->request->get('needId'));
        $currentUser = new User();
        $user = new User();
        $currentUser =  $userRepository->findOneBy(array('email' => $this->getUser()->getUserIdentifier()));
       
        $echange = new Echange();
        $echangeTemp = new Echange();
        $messages = [];
        //Verify if exchange exist
        $echangeTemp = $echangeRepository->findOneBy(array('owner' => $currentUser->getId(),  'besoin' => $besoin->getId()));
        if ($echangeTemp == null) {
            $echange->setOwner($this->getUser())
                ->setBesoin($besoin);
            $entityManager->persist($echange);
            $entityManager->flush();
        }
        else{
            $echange = $echangeTemp ;
        }
        

        $user =  $currentUser->getId() == $echange->getOwner()->getId() ?
            $besoin->getUser() : $echange->getOwner();


            $messages = $messageRepository->findBy( ['echange' => $echange->getId()],['id' => 'ASC'], 5  );
            $result = [];
            $lastMessage="";
            foreach ($messages as $value) {
                $lastMessage = $value->getContenu();
                $result[] = array(
                    'echange_id' => $echange->getId(),
                    'contenu' => $value->getContenu(),
                    'sender' => $value->getSender()->getId(),
                    'recipient' => $value->getRecipient()->getId(),
                    'currentUser' => $currentUser->getId(),
    
    
                );
            }

        return new Response(json_encode(array(
            'echangeId' => $echange->getId(),
            'messages' => $result,
            'user' => array(
                'name' => $user->getFirstName() . ' ' . $user->getLastName(),
                'profile' => 'img/' . $user->getProfile()
            )
        )));
    }
    #[Route('/needs/exchange/message', name: 'app_market_needs_message_show', methods: ['GET', 'POST'])]
    public function need_message_show(MessageRepository $messageRepository, Request $request, UserRepository $userRepository, EchangeRepository $echangeRepository): Response
    {
        $currentUser = new User();
        $user = new User();
        $currentUser =  $userRepository->findOneBy(array('email' => $this->getUser()->getUserIdentifier()));
         $echange = new Echange();
         $echange = $echangeRepository->find($request->request->get('id'));
        $user =  $currentUser->getId() == $echange->getOwner()->getId() ?
        $echange->getBesoin()->getUser() : $echange->getOwner();

        // return new Response(json_encode($currentUser->getEmail(). '/'.$echange->getId() ));

        $messages = $messageRepository->findBy( ['echange' => $echange->getId()],['id' => 'ASC'], 5  );
        $result = [];
        $lastMessage="";
        foreach ($messages as $value) {
            $lastMessage = $value->getContenu();
            $result[] = array(
                'echange_id' => $echange->getId(),
                'contenu' => $value->getContenu(),
                'sender' => $value->getSender()->getId(),
                'recipient' => $value->getRecipient()->getId(),
                'currentUser' => $currentUser->getId(),


            );
        }
    return new Response(json_encode(array(
        'echangeId' => $echange->getId(),
        'messages' => $result,
        'user' => array(
            'name' => $user->getFirstName() . ' ' . $user->getLastName(),
            'profile' => 'img/' . $user->getProfile()
        )
    )));
        /*
        

        $messages = $messageRepository->findBy(
            ['echange' => $echange->getId()],
            ['id' => 'ASC'],
            5

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

        );

        return new Response(
            json_encode($data)
        );*/
    }
    #[Route('/needs/exchange/message/send', name: 'app_market_needs_message_send', methods: ['GET', 'POST'])]
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
            $recipient = $echange->getBesoin()->getUser();
        } else {
            $sender = $echange->getBesoin()->getUser();
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
}
