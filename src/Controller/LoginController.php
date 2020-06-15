<?php

namespace App\Controller;

use App\Form\RemindPasswordFormType;
use App\Repository\UserRepository;
use LogicException;
use Nzo\UrlEncryptorBundle\Annotations\ParamDecryptor;
use Nzo\UrlEncryptorBundle\Annotations\ParamEncryptor;
use Nzo\UrlEncryptorBundle\UrlEncryptor\UrlEncryptor;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    private $encryptor;

    public function __construct(UrlEncryptor $encryptor)
    {
        $this->encryptor = $encryptor;
    }

    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
         if ($this->getUser()) {
             return $this->redirectToRoute('main_page');
         }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/forgotPassword", name="forgot_password")
     */
    public function forgotPassword( Request $request, MailerInterface $mailer)
    {


        $form = $this->createFormBuilder()
            ->add('email', EmailType::class, ['label' => 'your email'])
            ->add('submit',SubmitType::class,['label'=> 'submit'])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

           $url= $this->generateUrl('newPassword', [
                'email' => $this->encryptor->encrypt($form->getData()['email']),
                'timestamp' => $this->encryptor->encrypt(time()),
            ]);
            $email = new TemplatedEmail();
            $email->htmlTemplate('email/forgotPassword.html.twig')
                ->from('560a101814-d4e5b7@inbox.mailtrap.io')
//                ->to('560a101814-d4e5b7@inbox.mailtrap.io')
                ->to($form->getData()['email'])
                ->subject('zmien haslo')
                ->context([ 'generateNewPasswordUrl'=>$url
                ]);
            $mailer->send($email);
        }

        return $this->render('security/forgotPassword.html.twig',[
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/newPassword/{email}?{timestamp}", name="newPassword")
     */
    public function newPassword ( $email,Request $request,UserRepository $repository,UserPasswordEncoderInterface $passwordEncoder)
    {

        $form = $this->createForm(RemindPasswordFormType::class);
        $form->handleRequest($request);
        $email=$this->encryptor->decrypt($email);
        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $user=$repository->findOneByEmail($email);
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->redirectToRoute('app_logout');
        }

        return $this->render('security/newPassword.html.twig',[
            'form' => $form->createView()]);

    }
}
