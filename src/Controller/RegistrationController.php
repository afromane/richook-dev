<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\FileUploader;
use App\Service\MailerService;
use App\Security\EmailVerifier;
use App\Form\RegistrationFormType;
use Symfony\Component\Mime\Address;
use App\Security\LoginAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier) {}

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        Security $security,
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader,
        MailerService $mailerService
    ): Response {
        $user = new User();
        if ($request->isMethod('POST')) {
            /** @var string $plainPassword */
            $plainPassword = $request->request->get('password');
            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            /** @var UploadedFile $uploadFile */
            $profile = $request->files->get('profile');
            $sub = 'profiles/';
            $filenameProfile = $sub . $fileUploader->upload($profile, $sub);
            $user->setFirstName($request->request->get('firtName'))
                ->setLastName($request->request->get('lastName'))
                ->setTypePerson($request->request->get('typePerson'))
                ->setCity($request->request->get('city'))
                ->setGender($request->request->get('gender'))
                ->setEmail($request->request->get('email'))
                ->setAddresse($request->request->get('addresse'))
                ->setPhoneNumber($request->request->get('phoneNumber'))
                ->setCountry($request->request->get('country'))
                ->setProvince($request->request->get('province'))
                ->setProfile($filenameProfile)
                ->setActive(true)
                ->setDevise($request->request->get('devise'));


            $entityManager->persist($user);
            $entityManager->flush();

            $response = $mailerService->activateAccount($user);
            return $this->render('registration/confirmation_email.html.twig', [
            ]);
            // return $this->redirectToRoute('app_login');


            // return $this->redirectToRoute('app_login');
            //return $security->login($user, LoginAuthenticator::class, 'main');
        }

        return $this->render('registration/register.html.twig', [
            // 'registrationForm' => $form,
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            /** @var User $user */
            $user = $this->getUser();
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));
            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_register');
    }

    #[Route('/activate-account/{id}', name: 'app_activate_account', methods: ['GET'])]
    public function activateAccount(User $user,EntityManagerInterface $em): Response
    {
        $user->setVerified(true);
        $em->flush();
        return new Response(json_encode(true));
    }
}
