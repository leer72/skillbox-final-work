<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class BlaBlaArticleDashboardController extends AbstractController
{
    /**
    * @IsGranted("ROLE_USER") 
    * @Route("/dashboard", name="app_dashboard")
     */
    public function homepage()
    {
        return $this->render('dashboard/dashboard.html.twig');
    }
}
