<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Besoin;
use App\Form\BesoinType;
use App\Service\FileUploader;
use App\Repository\UserRepository;
use App\Repository\BesoinRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/besoin')]
final class BesoinController extends AbstractController
{
    #[Route(name: 'app_besoin_index', methods: ['GET'])]
    public function index(BesoinRepository $besoinRepository,UserRepository $userRepository): Response
    {

        $user = new User();
        $user = $userRepository->findOneBy(array('email'=>$this->getUser()->getUserIdentifier()));

        return $this->render('besoin/index.html.twig', [
            'besoins' => $besoinRepository->findBy(array('user'=>$user->getId())),
        ]);
    }

    #[Route('/new', name: 'app_besoin_new', methods: ['GET', 'POST'])]
    public function new(Request $request,CategoryRepository $categoryRepository,FileUploader $fileUploader, EntityManagerInterface $entityManager): Response
    {
        $besoin = new Besoin();

        if ($request->isMethod('POST')) {

            $sub = 'besoins/';

            /** @var UploadedFile $uploadFile */
            $image = $request->files->get('image');

            $filenameBesoinImage = $sub . $fileUploader->upload($image, $sub);

            $besoin->setImg($filenameBesoinImage)
                ->setPrivate( $request->request->get('isPrivate') == 'private' ? true : false)
                ->setDescription( $request->request->get('description'))
                ->setPeriode( $request->request->get('periode'))
                ->setCategory($categoryRepository->find( $request->request->get('category')))
                ->setLabel( $request->request->get('label'))
                ->setUser($this->getUser());


            $entityManager->persist($besoin);
            $entityManager->flush();

            return $this->redirectToRoute('app_besoin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('besoin/new.html.twig', [
            'categories' => $categoryRepository->findAll(),

        ]);
    }

    #[Route('/{id}', name: 'app_besoin_show', methods: ['GET'])]
    public function show(Besoin $besoin): Response
    {
        return $this->render('besoin/show.html.twig', [
            'besoin' => $besoin,
        ]);
    }

    #[Route('/{id}/transfer', name: 'app_besoin_transfer', methods: ['GET', 'POST'])]
    public function transfer(Request $request, Besoin $besoin, EntityManagerInterface $entityManager): Response
    {

        $besoin->setPrivate(!$besoin->isPrivate());
            $entityManager->flush();

            return $this->redirectToRoute('app_besoin_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/edit', name: 'app_besoin_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Besoin $besoin, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BesoinType::class, $besoin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_besoin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('besoin/edit.html.twig', [
            'besoin' => $besoin,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_besoin_delete', methods: ['POST'])]
    public function delete(Request $request, Besoin $besoin, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$besoin->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($besoin);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_besoin_index', [], Response::HTTP_SEE_OTHER);
    }
}
