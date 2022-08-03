<?php

namespace App\Controller;

use DateTime;
use App\Entity\Word;
use App\Entity\Article;
use App\Entity\Keyword;
use App\Entity\Module;
use App\Service\Mailer;
use App\Entity\Subscription;
use App\Form\ArticleFormType;
use App\Form\ModuleFormType;
use App\Form\ProfileFormType;
use App\Service\FileUploader;
use App\Repository\ArticleRepository;
use App\Repository\ModuleRepository;
use App\Service\ThemeContentProvider;
use App\Service\ArticleContentProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use App\Repository\SubscriptionRepository;
use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\BlaBlaArticleSubscriptionProvider;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class BlaBlaArticleDashboardController extends AbstractController
{
    /**
    * @IsGranted("ROLE_USER") 
    * @Route("/dashboard", name="app_dashboard")
     */
    public function homepage(
        ArticleRepository $articleRepository,
        BlaBlaArticleSubscriptionProvider $subscriptionsProvider,
        SubscriptionRepository $subscriptionRepository
    ) {
        $allArticles = $articleRepository->findAllByUserCount($this->getUser());
        
        $articlesPerMonth = $articleRepository->findByCreatedAtCount(new DateTime('-1 month'), $this->getUser());
        
        $lastArticle = $articleRepository->findOneBy(['author' => $this->getUser()->getId()], ['createdAt' => 'DESC']);
        
        $subscription = $subscriptionsProvider->getSubscriptionByUser($this->getUser());

        $subscriptionName = $subscription->getName();

        $subscriptionsInDB = $subscriptionRepository->findBy(['user' => $this->getUser()->getId()], ['level' => 'DESC']);
        
        $subscriptionEnd = null;
        
        foreach($subscriptionsInDB as $subscriptionInDB) {
            if($subscription->getLevel() == $subscriptionInDB->getLevel()) {
                $subscriptionEnd = $subscriptionInDB->getCreatedAt()->add($subscription->getDuration());
                break;
            }
        }
        
        if(new DateTime('+4 days') <= $subscriptionEnd) {
            $subscriptionEnd = null;
        }

        return $this->render('dashboard/dashboard.html.twig', [
            'allArticles' => $allArticles[0]['allArticles'],
            'articlesPerMonth' => $articlesPerMonth[0]['allPerPeriod'],
            'subscriptionName' => $subscriptionName,
            'subscriptionEnd' => $subscriptionEnd,
            'lastArticle' => $lastArticle,
        ]);
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
    * @Route("/dashboard/subscription/{level}", name="app_dashboard_subscription")
    */
    public function subscription(
        BlaBlaArticleSubscriptionProvider $subscriptionsProvider,
        EntityManagerInterface $em,
        Mailer $mailer,
        int $level = 1
    ) {
        $successMessage = null;
        
        if($level > 1) {
            $newSubscription = (new Subscription())
                ->setLevel($level)
                ->setUser($this->getUser())
            ;

            $em->persist($newSubscription);
            $em->flush();

            $mailer->sendSubscriptionMail($this->getUser());

            $successMessage = 'Подписка ' . $subscriptionsProvider->getSubscriptionByUser($this->getUser())->getName() . ' оформлена, до ' . (new DateTime('+1 week'))->format('d.m.Y');
        }
        
        return $this->render('dashboard/subscription.html.twig', [
            'currentLevel' => $subscriptionsProvider->getSubscriptionByUser($this->getUser())->getLevel(),
            'successMessage' => $successMessage,
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
        BlaBlaArticleSubscriptionProvider $subscriptionsProvider,
        int $id = 0
    )
    {
        if($id) {
            $article = $articleRepository->findOneBy(['id' => $id]);
        } else {
            $article = new Article();
            $word = new Word();
            $article->addWord($word);
        }
        
        $avalibleWords = $subscriptionsProvider->getSubscriptionByUser($this->getUser())->getAvalibleWords();

        $avalibleCreateArticle = $subscriptionsProvider->canUserCreateArticle($this->getUser());

        $form = $this->createForm(ArticleFormType::class, $article);
        
        if($avalibleCreateArticle) {
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
        }

        $errors = $form->getErrors();

        return $this->render('dashboard/create_article.html.twig', [
            'articleForm' => $form->createView(),
            'article' => $article,
            'errors' => $errors,
            'avalibleCreateArticle' => $avalibleCreateArticle,
            'avalibleWords' => $avalibleWords,
        ]);
    }

    /**
    * @IsGranted("ROLE_USER") 
    * @Route("/dashboard/profile", name="app_dashboard_profile")
     */
    public function profile(
        EntityManagerInterface $em, 
        Request $request, 
        UserPasswordEncoderInterface $passwordEncoder, 
        Mailer $mailer,
        UserRepository $userRepository
    ) {
        $form = $this->createForm(ProfileFormType::class, $this->getUser());
        $form->handleRequest($request);

        $success = false;
        $changeEmail = false;

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $form->getData();

            if($password = $form->get('plainPassword')->getData()) {
                $user
                    ->setPassword($passwordEncoder->encodePassword(
                        $user,
                        $password
                    ))
                ;
            }

            if(($form->get('email')->getData() !== $user->getEmail()) && $form->get('email')->getData() != null) {
                $isExist = $userRepository->getCountUserByEmail($form->get('email')->getData());
                
                if(0 === $isExist[0]['users']) {
                    $user->setNewEmail($form->get('email')->getData());
                    $user->setEmailToken(sha1(uniqid('token')));
                    $mailer->sendChangeEmail($user);

                    $changeEmail = true;
                }
            }

            $em->persist($user);
            $em->flush();

            $success = true;
        } elseif(! $form->isSubmitted()) {
            $form->get('email')->setData($this->getUser()->getEmail());
        }
        
        return $this->render('dashboard/profile.html.twig', [
            'userForm' => $form->createView(),
            'apiToken' => $this->getUser()->getApiToken()->getToken(),
            'success' => $success,
            'changeEmail' => $changeEmail,
        ]);
    }

    /**
    * @IsGranted("ROLE_USER") 
    * @Route("/dashboard/modules", name="app_dashboard_modules")
     */
    public function modules(
        Request $request, 
        EntityManagerInterface $em, 
        ModuleRepository $moduleRepository, 
        PaginatorInterface $paginator,
        BlaBlaArticleSubscriptionProvider $subscriptionProvider
    ) {
        if(! $subscriptionProvider->canUserCreateModules($this->getUser())) {
            
            return $this->redirectToRoute('app_dashboard_subscription');
        }
        
        $form = $this->createForm(ModuleFormType::class, new Module());
        $form->handleRequest($request);

        $success = false;

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Module $module */
            $module = $form->getData();
            $em->persist($module);
            
            $user = $this->getUser();
            $user->addModule($module);

            $em->persist($user);
            $em->flush();

            $success = true;
        }

        $pagination = $paginator->paginate(
            $moduleRepository->findBy(['author' => $this->getUser()->getId()]),
            $request->query->getInt('page', 1),
            10
        );
        
        return $this->render('dashboard/modules.html.twig', [
            'moduleForm' => $form->createView(),
            'pagination' => $pagination,
            'success' => $success,
        ]);
    }

    /**
    * @IsGranted("ROLE_USER") 
    * @Route("/dashboard/module/remove/{id}", name="app_dashboard_module_remove")
     */
    public function moduleRemove(
        EntityManagerInterface $em, 
        Module $module
    ) {
        $user = $this->getUser();
        $user->removeModule($module);

        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('app_dashboard_modules');
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
                if($word->getWord() == null || $word->getCount() == null) {
                    $article->removeWord($word);
                } else {
                /** @var Word $word */
                $word->setArticle($article);
                $em->persist($word);
                }
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
