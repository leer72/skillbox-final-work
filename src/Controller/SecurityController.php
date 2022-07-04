<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserRegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils, Request $request): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        $rememberMe = $request->getSession()->get('remember_me');
        
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername, 
            'error' => $error,
            'remember_me' =>$rememberMe
        ]);
    }

     /**
     * @Route("/register", name="app_register")
     */
    public function register(
        Request $request, 
        UserPasswordEncoderInterface $passwordEncoder,
        EntityManagerInterface $em
    ) {
        $form = $this->createForm(UserRegistrationFormType::class);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            
            /** @var UserRegistrationFormType $userModel */
            $userModel = $form->getData();
            $user = new User();
            
            $user
                ->setEmail($userModel->email)
                ->setFirstName($userModel->firstName)
                ->setPassword($passwordEncoder->encodePassword(
                    $user,
                    $userModel->plainPassword
            ));

            $em->persist($user);
            $em->flush();

            return $this->render(
                'security/register.html.twig',
                [
                    'registrationForm' => $form->createView(),
                    'success' => true,
                ]
            );
        }

        return $this->render(
            'security/register.html.twig',
            [
                'registrationForm' => $form->createView(),
                'success' => '',
            ]
        );
    }
    
    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
