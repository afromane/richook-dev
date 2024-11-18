<?php

namespace App\Controller;

use App\Entity\Potentiel;
use App\Entity\User;
use App\Form\PotentielType;
use App\Repository\CategoryRepository;
use App\Repository\PotentielRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Service\FileUploader;

#[Route('/potentiel')]
final class PotentielController extends AbstractController
{

    #[Route(name: 'app_potentiel_index', methods: ['GET'])]
    public function index(PotentielRepository $potentielRepository): Response
    {
        // dd( $potentielRepository->findAll());
        return $this->render('potentiel/index.html.twig', [
            'potentiels' => $potentielRepository->findAll(),
        ]);
    }

    #[Route('/bourse', name: 'app_potentiel_index_marche', methods: ['GET'])]
    public function marcher(PotentielRepository $potentielRepository, UserRepository $userRepository): Response
    {
        //On recupere toutes les potentiels public en excluant pour l'utilisateur courant
        $currentUser = new User();
        $currentUser =  $userRepository->findOneBy(array('email' => $this->getUser()->getUserIdentifier()));

        $potentiels = [];
        foreach ($potentielRepository->findBy(array('isPrivate' => false)) as  $value) {

            if ($currentUser->getId() != $value->getUser()->getId()) {
                $potentiels[] = $value;
            }
        }
        return $this->render('potentiel/marche.html.twig', [
            'potentiels' => $potentiels ,
        ]);
    }
    #[Route('/new', name: 'app_potentiel_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        CategoryRepository $categoryRepository,
        FileUploader $fileUploader
    ): Response {
        $potentiel = new Potentiel();
        if ($request->isMethod('POST')) {
            $type = $request->request->get('type');
            $code = $request->request->get('code');
            $name = $request->request->get('name');
            $category = $request->request->get('category');
            $value = $request->request->get('value');
            $periodicity = $request->request->get('periodicity');
            $affectation = $request->request->get('affectation');
            $description = $request->request->get('description');

            $sub = 'potentiels/';

            /** @var UploadedFile $uploadFile */
            $profile = $request->files->get('profile');

            $filenameProfile = $sub . $fileUploader->upload($profile, $sub);


            $fileNames = $request->request->get('file_name');
            $fileNamesArray = explode('#', $fileNames);
            $fileDescriptions = $request->request->get('file_description');
            $fileDescriptionArray = explode('#', $fileDescriptions);
            $files = $request->files->get('file');
            $documents = [];
            $i = 0;
            if ($files) {
                foreach ($files as $index => $file) {


                    $filename = $sub . $fileUploader->upload($file, $sub);
                    $documents[] = array(
                        'name' => $fileNamesArray[$index],
                        'description' => $fileDescriptionArray[$index],
                        'path' => $filename
                    );
                }
            }

            $potentiel->setImg($filenameProfile)
                ->setJustificatif($documents)
                ->setActive(true)
                ->setPrivate(true)
                ->setDescription($description)
                ->setAffectation($affectation)
                ->setPeriodicity($periodicity)
                ->setCategory($categoryRepository->find($category))
                ->setValue($value)
                ->setName($name)
                ->setUser($this->getUser())
                ->setCode('');



            $entityManager->persist($potentiel);
            $entityManager->flush();

            return $this->redirectToRoute('app_potentiel_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('potentiel/new.html.twig', [
            'potentiel' => $potentiel,
            // 'form' => $form,
            'categories' => $categoryRepository->findAll()
        ]);
    }

    #[Route('/{id}', name: 'app_potentiel_show', methods: ['GET'])]
    public function show(Potentiel $potentiel): Response
    {
        return $this->render('potentiel/show.html.twig', [
            'potentiel' => $potentiel,
        ]);
    }

    #[Route('/{id}/transfer', name: 'app_potentiel_transfer', methods: ['GET', 'POST'])]
    public function transfer(Request $request, Potentiel $potentiel, EntityManagerInterface $entityManager): Response
    {

        $potentiel->setPrivate(!$potentiel->isPrivate());
            $entityManager->flush();

            return $this->redirectToRoute('app_potentiel_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/{id}/edit', name: 'app_potentiel_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Potentiel $potentiel, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PotentielType::class, $potentiel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_potentiel_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('potentiel/edit.html.twig', [
            'potentiel' => $potentiel,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_potentiel_delete', methods: ['POST'])]
    public function delete(Request $request, Potentiel $potentiel, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $potentiel->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($potentiel);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_potentiel_index', [], Response::HTTP_SEE_OTHER);
    }
}
