<?php

namespace App\Controller;

use App\Entity\Article;
use App\Service\FileUploader;
use App\Service\ArticleSetContent;
use App\Service\ThemeContentProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\BlaBlaArticleSubscriptionProvider;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @IsGranted("ROLE_USER")
 */
class ApiBlaBlaArticleController extends AbstractController
{
    /**
     * @Route("/api/article_content", methods={"POST"}, name="app_api_create_aricle")
     */
    public function getArticle(
        Request $request, 
        BlaBlaArticleSubscriptionProvider $subscriptionsProvider, 
        ThemeContentProvider $themeContentProvider,
        EntityManagerInterface $em,
        FileUploader $articleFileUploader,
        string $uploadPath,
        ArticleSetContent $articleSetContent
    ) {
        if(! $subscriptionsProvider->canUserCreateArticle($this->getUser())) {
            
            return $this->json(['error' => 'Достигнут лимит создания статьей']);
        }

        $data = json_decode($request->getContent(), true);
        
        $theme = ($data['theme'] ?? null) ? $themeContentProvider->findThemeBySlug($data['theme']) : null;
        $title = ($data['title'] ?? null) ? '<h1> ' . $data['title'] . ' </h1>' : '<h1> </h1>';
        $size = $data['size'] ?? null;
        $keywordRaw = ($data['keyword'] ?? null);
        $words = $data['words'] ?? null;
        $images = (($data['images'] ?? null) && $subscriptionsProvider->getSubscriptionByUser($this->getUser())->getAvalibleImages()) ? $data['images'] : null;
        
        $avalibleWords = $subscriptionsProvider->getSubscriptionByUser($this->getUser())->getAvalibleWords();

        if($keywordRaw) { //Если подписка не позволяет использовать словоформы - берем только основную форму
            $keywordRaw = $subscriptionsProvider->getSubscriptionByUser($this->getUser())->getAvalibleKeywordMorphs() ? $keywordRaw : array($keywordRaw[0]);
        }

        $article = new Article();

        if(! $avalibleWords) {
                if(count($words)) {
                    $words = array($words[0]);
                }
        }
        
        if($images) {
            foreach($images as $image) {
                $filename = basename($image);
                file_put_contents($uploadPath . $filename, file_get_contents($image));
                $article->setImageFilename(
                    $articleFileUploader->uploadFile(
                        new UploadedFile($uploadPath . $filename, $filename)
                ));
            }
        }       
        
        $articleSetContent->articleSetContent($article, $title, $theme, $size, $size, $words, $keywordRaw, $this->getUser(), $em);
        
        $em->persist($article);
        $em->flush();
        
        return $this->json([
            'title' => $article->getTitle(),
            'description' => mb_convert_encoding(substr($article->getBody(), 0, 200), 'UTF-8', 'UTF-8'),
            'content' => mb_convert_encoding($article->getBody(), 'UTF-8', 'UTF-8'),
        ]);
    }
}
