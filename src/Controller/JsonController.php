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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class JsonController extends AbstractController
{
    private JWTTokenManagerInterface $tokenManager;
    private TokenStorageInterface $tokenStorage;

    public function __construct() {}
    // #[Route(path: '/json/login', methods: ['POST'] )]
    // public function login(AuthenticationUtils $authenticationUtils): JsonResponse
    // {
    //      if ($this->getUser()) {
    //         //  return $this->redirectToRoute('app_dashboard');
    //     return new JsonResponse(array('success'=>'cool'));

    //      }

    //     // get the login error if there is one
    //     $error = $authenticationUtils->getLastAuthenticationError();
    //     // last username entered by the user
    //     $lastUsername = $authenticationUtils->getLastUsername();
    //     return new JsonResponse(array('success'=>'cbadool'));


    // }

    // #[Route(path: '/json/login', )]
    // public function login(AuthenticationUtils $authenticationUtils): JsonResponse
    // {
    //     return new JsonResponse([
    //         'message' => 'Authentication successful!',
    //     ]);
    // }
    #[Route('/json/besoin/all')]
    public function index(BesoinRepository $besoinRepository): Response
    {
        $result = [];
        $data = $besoinRepository->findAll();
        foreach ($data as  $value) {
            $result[] = array(
                'id' => $value->getId(),
                'label' => $value->getLabel(),
                'img' => $value->getImg(),
                'description' => $value->getDescription(),
                'category' => array(
                    'id' => $value->getCategory()->getId(),
                    'label' => $value->getCategory()->getLabel(),

                ),
                'user' => array(
                    'id' => $value->getUser()->getId(),
                    'firstName' => $value->getUser()->getFirstName(),
                    'lastName' => $value->getUser()->getLastName(),

                ),
            );
        }

        return new Response(json_encode($result));
    }

    #[Route('/json/exchange/start', methods: ['GET', 'POST'])]
    public function start(Request $request, EchangeRepository $echangeRepository, BesoinRepository $besoinRepository, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);
        
        $besoin = new Besoin();
        $besoin = $besoinRepository->find($data['needId']);
        $currentUser = new User();
        $user = new User();
        $currentUser =  $userRepository->find(1);

        $echange = new Echange();
        $echangeTemp = new Echange();
        //Verify if exchange exist
        $echangeTemp = $echangeRepository->findOneBy(array('owner' => $currentUser->getId(),  'besoin' => $besoin->getId()));
        if ($echangeTemp == null) {
            $echange->setOwner($currentUser)
                ->setBesoin($besoin);
            $entityManager->persist($echange);
            $entityManager->flush();
        } else {
            $echange = $echangeTemp;
        }




        return new Response(json_encode(array(
            'echangeId' => $echange->getId(),
        )));
    }
    #[Route('/json/exchange/messages/{id}', methods: ['GET', 'POST'])]
    public function exchangeMessage($id,Request $request,MessageRepository $messageRepository, EchangeRepository $echangeRepository, BesoinRepository $besoinRepository, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        //  $data = json_decode($request->getContent(), true);
       

        $currentUser =  $userRepository->find(1);
        $echange = new Echange();
        $echange = $echangeRepository->find($id);

        $messages = $messageRepository->findBy(['echange' => $echange->getId()], ['id' => 'ASC'], 20);
        $result = [];
        $lastMessage = "";
        foreach ($messages as $value) {
            $lastMessage = $value->getContenu();
            $result[] = array(
                'echange_id' => $id,
                'contenu' => $value->getContenu(),
                'sender' => $value->getSender()->getId(),
                'recipient' => $value->getRecipient()->getId(),
                'currentUser' => $currentUser->getId(),


            );
        }
        // Verify if exchange it for Potential or need
        $infos = [];
        $isPotential = true;
        if ($echange->getPotentiel() == null && $echange->getBesoin()) {

            $isPotential = false;
            $infos = array(
                'name' => $echange->getBesoin()->getLabel(),
                'description' => $echange->getBesoin()->getDescription(),
                'image' => 'img/' . $echange->getBesoin()->getImg(),
                'user_id' => $echange->getBesoin()->getUser()->getId()
            );
        } else {
            array(
                'name' => $echange->getPotentiel()->getName(),
                'description' => $echange->getPotentiel()->getDescription(),
                'image' => 'img/' . $echange->getPotentiel()->getImg(),
                'user_id' => $echange->getPotentiel()->getUser()->getId()
            );
        }
        $owner =[];

        if( $currentUser->getId() == $echange->getOwner()->getId())
        {
            if($echange->getPotentiel() == null && $echange->getBesoin())
            {
                 $owner = array(
                    'name' =>   $echange->getBesoin()->getUser()->getFirstName() . ' ' . $echange->getBesoin()->getUser()->getLastName(),
                    'profile' => 'img/' . $echange->getBesoin()->getUser()->getProfile()
                 );
            }
            else{
                $owner = array(
                    'name' =>   $echange->getPotentiel()->getUser()->getFirstName() . ' ' . $echange->getPotentiel()->getUser()->getLastName(),
                    'profile' => 'img/' . $echange->getPotentiel()->getUser()->getProfile()
                 );
            }

        }
        else{
                 $owner = array(
                    'name' =>   $echange->getOwner()->getFirstName() . ' ' . $echange->getOwner()->getLastName(),
                    'profile' => 'img/' . $echange->getOwner()->getProfile()
                 );
        }
        $data = array(
            'messages' => $result,
            'lastMessage' => $lastMessage,
            'isPotential' => $isPotential,
            'infos' => $infos,
            'owner' => $owner
        );

        return new Response(
            json_encode($data)
        );



    }

    
    #[Route('/json/message/send',  methods: ['GET', 'POST'])]
    public function send_message(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository, EchangeRepository $echangeRepository): Response
    {
        // $currentUser =  $userRepository->findOneBy(array('email' => $this->getUser()->getUserIdentifier()));
          $data = json_decode($request->getContent(), true);

        $currentUser =  $userRepository->find(2);

        $echange = new Echange();
        $message = new Message();
        $sender = new User();
        $recipient = new User();
        $echange = $echangeRepository->find($data['echangeId']);

        if ($echange->getOwner()->getId() == $currentUser->getId()) {
            $sender = $this->getUser();
            $recipient = $echange->getPotentiel() != null ?$echange->getPotentiel()->getUser() : $echange->getBesoin()->getUser();
        } else {
            $sender = $echange->getPotentiel() != null ?$echange->getPotentiel()->getUser() : $echange->getBesoin()->getUser();
            $recipient =  $currentUser;
        }
        $message->setEchange($echange)
            ->setContenu($data['message'])
            ->setSender($sender)
            ->setRecipient($recipient);


        $entityManager->persist($message);
        $entityManager->flush();


        return new Response(
            json_encode('successfully')
        );
    }
}
