<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Nzo\UrlEncryptorBundle\Annotations\ParamDecryptor;
use Nzo\UrlEncryptorBundle\Annotations\ParamEncryptor;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Repository\UserRepository;
use Nzo\UrlEncryptorBundle\UrlEncryptor\UrlEncryptor;


class RegistrationController extends AbstractController
{

    private $encryptor;

    public function __construct(UrlEncryptor $encryptor)
    {
        $this->encryptor = $encryptor;
    }

    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, MailerInterface $mailer): Response {

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);

            $confirmationURL = $this->generateUrl('confirm_registration', [
                'createdAt'=>$this->encryptor->encrypt($user->getCreatedAt()->getTimestamp()),
                'email'=>$this->encryptor->encrypt($user->getEmail())
            ]);

            $email = new TemplatedEmail();
            $email->htmlTemplate('email/registerConfirmation.html.twig')
                ->from('560a101814-d4e5b7@inbox.mailtrap.io')
//                ->to('560a101814-d4e5b7@inbox.mailtrap.io')
                ->to($user->getEmail())
                ->subject('test')
                ->context([
                    'confirmationUrl' => $confirmationURL,
                    'userName' => $user->getName()
                ]);

            $entityManager->flush();
            $mailer->send($email);

            return $this->redirectToRoute('waiting_for_confirm');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/register/confirmation/{createdAt}/{email}", name="confirm_registration")
     * @ParamDecryptor(params={"createdAt","email"})
     */
    public function confirmRegistration($createdAt, $email, Request $request, UserRepository $repository) {


        $user = $repository->findOneByEmailAndCreatedAtTimestamp($email,$createdAt);
        if($user->getAccountConfirmed() == false){

            if (time() - $createdAt < 3600) {
                $this->render('registration/confirm.html.twig');
                $user->setAccountConfirmed(true);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                return $this->redirectToRoute('app_login');

            } else{
                $form = $this->createFormBuilder()
                    ->add('generate', SubmitType::class, ['label' => 'generate confirm email'])
                    ->getForm();
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    return $this->redirectToRoute('generate_confirmation_mail', ['email' => $this->encryptor->encrypt($email)]);
                }
            }
        }
        return $this->render('registration/generateNewConfirmationLink.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/register/generate/confirmation/{email}", name="generate_confirmation_mail")
     * @ParamEncryptor(params="email")
     */
    public function generateConfirmationMail($email, Request $request, MailerInterface $mailer)
    {

        $confirmationURL = $this->generateUrl('confirm_registration',
            ['createdAt' => $this->encryptor->encrypt(time()), 'email' => $this->encryptor->encrypt($email)]);

        $confirmationEmail = new TemplatedEmail();
        $confirmationEmail->htmlTemplate('email/registerConfirmation.html.twig')
            ->from('560a101814-d4e5b7@inbox.mailtrap.io')
            ->to('560a101814-d4e5b7@inbox.mailtrap.io')
            ->subject('test')
            ->context(['confirmationUrl' => $confirmationURL, 'userName' => "bartek"]);

        $mailer->send($confirmationEmail);
        return $this->redirectToRoute('waiting_for_confirm');
    }

    /**
     * @Route("/register/pleaseConfirmEmail/", name="waiting_for_confirm")
     */
    public function pleasConfirmRegistration(Request $request)
    {
        return $this->render('registration/waitingForConfirm.html.twig');
    }
}
