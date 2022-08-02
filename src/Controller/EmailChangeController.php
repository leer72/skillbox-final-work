<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Liip\ImagineBundle\Exception\Config\Filter\NotFoundException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EmailChangeController extends AbstractController
{
    /**
     * @Route("/dashboard/email/change/{emailToken}", name="app_email_change")
     */
    public function emailChange(User $user, EntityManagerInterface $em)
    {
        if($user) {
            $user->setEmail($user->getNewEmail());
            $user->setNewEmail(null);
            $user->setEmailToken(null);

            $em->persist($user);
            $em->flush();
        } else {
            new NotFoundException('Недействительная ссылка');
        }
        
        return $this->render('email_change_success.html.twig');
    }
}
