<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use App\Security\LoginAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use App\Service\FileUploader;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier) {}

    #[Route('/email')]
    public function maile(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        /** @var string $plainPassword */
        $plainPassword = "1111";

        // encode the plain password
        $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
        $user->setFirstName('FN')
            ->setLastName('LN')
            ->setEmail('issamanel05@gmail.com')
            ->setTypePerson('TP')
            ->setCity('Yd')
            ->setGender('G')
            ->setProvince('Pro')
            ->setProfile('Pr')
            ->setCountry('C')
            ->setActive(true)
            ->setDevise('D');


        // generate a signed url and email it to the user
        $this->emailVerifier->sendEmailConfirmation(
            'app_verify_email',
            $user,
            (new TemplatedEmail())
                ->from(new Address('issamanel05@gmail.com', 'Manel Mail Bot'))
                ->to((string) $user->getEmail())
                ->subject('Please Confirm your Email')
                ->htmlTemplate('registration/confirmation_email.html.twig')
        );

        return $this->redirectToRoute('app_register');
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager
    ,FileUploader $fileUploader): Response
    {
        $user = new User();
        // $form = $this->createForm(RegistrationFormType::class, $user);
        // $form->handleRequest($request);

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
                ->setCountry($request->request->get('country'))
                 ->setProvince($request->request->get('province'))
                 ->setProfile($filenameProfile)
                 ->setActive(true)
                 ->setDevise($request->request->get('devise'));


            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address('issamanel05@gmail.com', 'Manel Mail Bot'))
                    ->to((string) $user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_login');
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
}
