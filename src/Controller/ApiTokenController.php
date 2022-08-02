<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class ApiTokenController extends AbstractController
{
    /**
     * @IsGranted("ROLE_USER")
     * @Route("/dashboard/api/token", methods={"POST"}, name="app_dashboard_api_token")
     */
    public function apiToken(EntityManagerInterface $em)
    {
        $user = $this->getUser();
        $token = $user->getApiToken();
        $token->setToken(sha1(uniqid('token')));
        
        $apiToken = $token->getToken();

        $em->persist($token);
        $em->flush();

        return $this->json(['apiToken' => $apiToken]);
    }
}
