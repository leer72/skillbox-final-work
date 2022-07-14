<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Keyword;
use App\Form\TryFormType;
use App\Repository\UserRepository;
use App\Service\ArticleContentProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BlaBlaArticleController extends AbstractController
{
    /**
     * @Route("/", name="app_homepage")
     */
    public function homepage()
    {
        return $this->render('homepage.html.twig');
    }

    /**
     * @Route("/try", name="app_try")
     */
    public function try(
        EntityManagerInterface $em, 
        Request $request,
        ArticleContentProvider $contentProvider,
        UserRepository $userRepository
    )
    {
        $article = new Article;
        $form = $this->createForm(TryFormType::class, $article);
        $article = $this->handleFormRequest($form, $em, $request, $contentProvider, $userRepository); 

        return $this->render('try.html.twig', [
            'articleForm' => $form->createView(),
            'article' => $article,
        ]);
        
    }

    public function handleFormRequest(
        FormInterface $form, 
        EntityManagerInterface $em, 
        Request $request,
        ArticleContentProvider $contentProvider,
        UserRepository $userRepository
    ) {
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Article $article */
            $article = $form->getData();
            
            $keyword = (new Keyword())->setKeyword($form->get('keyword')->getData());
            
            $article->setKeyword($keyword);

            $slugger = new AsciiSlugger();
            
            if($article->getTitle()) {
                $article->setSlug($slugger->slug($article->getTitle()) . '_' . uniqid());
            } else {
                $article->setSlug($slugger->slug(uniqid()));
            }

            $article
                ->setBody($contentProvider->getBody($keyword))
                ->setTitle($contentProvider->getTitle($article->getTitle(), $keyword))
                ->setAuthor($userRepository->findOneBy(['email' => 'non_auth_user@blablaarticle.ru']))
            ;

            $em->persist($article);
            $em->flush();
            
            return $article;
        }

        return null;
    }
}
