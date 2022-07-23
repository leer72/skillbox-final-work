<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Keyword;
use App\Entity\Word;
use App\Form\ArticleFormType;
use App\Repository\ArticleRepository;
use App\Service\ArticleContentProvider;
use App\Service\FileUploader;
use App\Service\ThemeContentProvider;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\String\Slugger\AsciiSlugger;

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

    /**
    * @IsGranted("ROLE_USER") 
    * @Route("/dashboard/history", name="app_dashboard_history")
    */
    public function history(
        PaginatorInterface $paginator, 
        ArticleRepository $articleRepository,
        Request $request
    ) {
        $pagination = $paginator->paginate(
            $articleRepository->findAllByUser($this->getUser()),
            $request->query->getInt('page', 1),
            10
        );
        
        return $this->render('dashboard/history.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
    * @IsGranted("ROLE_USER") 
    * @Route("/dashboard/article/{id}/detail", name="app_dashboard_article_detail")
    */
    public function articleDetail(
        Article $article
    ) {
        return $this->render('dashboard/article_detail.html.twig', [
            'article' => $article,
        ]);
    }

    /**
    * @IsGranted("ROLE_USER") 
    * @Route("/dashboard/create/article/{id}", name="app_create_article")
     */
    public function createArticle(
        EntityManagerInterface $em, 
        Request $request,
        ArticleContentProvider $contentProvider,
        ThemeContentProvider $themeContentProvider,
        FileUploader $articleFileUploader,
        ArticleRepository $articleRepository,
        int $id = 0
    )
    {
        if($id) {
            $article = $articleRepository->findOneBy(['id' => $id]);
        } else {
            $article = new Article();
        }
        
        
        $form = $this->createForm(ArticleFormType::class, $article);
        
        $article = $this->handleFormRequest(
            $form, 
            $em, 
            $request, 
            $contentProvider, 
            $themeContentProvider,
            $articleFileUploader,
            $article
        );

        if (! $form->isSubmitted()) {
            if($article->getKeyword()) {
                for($i = 0; $i < count($article->getKeyword()->getKeyword()); $i++) {
                    $form->get('keyword_' . $i)->setData($article->getKeyword()->getKeyword()[$i]);
                }
            }

            if($article->getTheme()) {
                $form->get('theme')->setData($article->getTheme());
            }
        }
       
        $errors = $form->getErrors();

        return $this->render('dashboard/create_article.html.twig', [
            'articleForm' => $form->createView(),
            'article' => $article,
            'errors' => $errors,
        ]);
    }

    public function handleFormRequest(
        FormInterface $form, 
        EntityManagerInterface $em, 
        Request $request,
        ArticleContentProvider $contentProvider,
        ThemeContentProvider $themeContentProvider,
        FileUploader $articleFileUploader,
        Article $article
    ) {
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Article $article */
            $article = $form->getData();

            $themeSlug = $form->get('theme')->getData();

            $theme = ($themeSlug) ? $themeContentProvider->findThemeBySlug($themeSlug) : null;
            
            $keywordFromForm = [];
            for($i = 0; $i <= 6; $i++) {
                if($form->get('keyword_' . $i)->getData()) {
                    $keywordFromForm[] = $form->get('keyword_' . $i)->getData();
                } elseif(count($keywordFromForm) > 0) {
                    $keywordFromForm[] = $keywordFromForm[0];
                }
            }
            
            if((count($keywordFromForm) > 0) && count($keywordFromForm)) {
                $keyword = (new Keyword)->setKeyword($keywordFromForm);
            } else {
                $keyword = null;
            }
            
            foreach($article->getWords() as $word) {
                /** @var Word $word */
                $word->setArticle($article);
                $em->persist($word);
            }
            
            $article
                ->setKeyword($keyword)
                ->setAuthor($this->getUser())
            ;
            
            $articleLength = null;

            $sizeFrom = $form->get('sizeFrom')->getData();
            $sizeTo = $form->get('sizeTo')->getData();
            
            if($sizeFrom && $sizeTo) {
                $articleLength = rand($sizeFrom, $sizeTo);
            } elseif($sizeFrom) {
                $articleLength = $sizeFrom;
            } elseif($sizeTo) {
                $articleLength = $sizeTo;
            }
            
            $slugger = new AsciiSlugger();
            
            if($article->getTitle()) {
                $article->setSlug($slugger->slug($article->getTitle()) . '_' . uniqid());
            } elseif($theme) {
                $article->setSlug($slugger->slug($theme->getTitle($keyword))->toString(). '_' . uniqid());
            } else {
                $article->setSlug($slugger->slug(uniqid()));
            }

            if(! $article->getId()) {
                /** @var UploadedFile|null $image */
                $images = $form->get('image')->getData();
                foreach($images as $image) {
                    $article->setImageFilename($articleFileUploader->uploadFile($image));
                }
            }

            if($theme) {
                $article
                    ->setBody($theme->getParagraphs($keyword))
                    ->setTitle($theme->getTitle($keyword))
                    ->setTheme($theme->getSlug())
                ;
            } else {
                $article
                    ->setBody($contentProvider->getBody($article, $article->getWords(), $articleLength))
                    ->setTitle($contentProvider->getTitle(($article->getTitle()) ? $article->getTitle() : '', ($keyword) ? $keyword : new Keyword()))
                ;
            }

            $em->persist($article);
            $em->flush();
            
            return $article;
        }

        return $article;
    }
}
