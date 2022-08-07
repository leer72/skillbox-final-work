<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Word;
use App\Entity\Article;
use App\Entity\Keyword;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

class ArticleSetContent
{
    private static $defaultArticleLength = 3;
    
    private $contentProvider;

    public function __construct(ArticleContentProvider $contentProvider)
    {
        $this->contentProvider = $contentProvider;
    }

    public function articleSetContent(
        Article &$article, 
        ?string $title,
        ?ThemeInterface $theme,
        ?int $sizeFrom, 
        ?int $sizeTo,
        ?array $words,
        ?array $keywordRaw,
        User $user,
        EntityManagerInterface $em
    ) {
        $arrayForKeyword = [];
        for($i = 0; $i <= 6; $i++) {
            if(isset($keywordRaw[$i])) {
                $arrayForKeyword[] = $keywordRaw[$i];
            } elseif(count($arrayForKeyword) > 0) {
                $arrayForKeyword[] = $arrayForKeyword[0];
            }
        }

        $keyword = count($arrayForKeyword) ? (new Keyword)->setKeyword($arrayForKeyword) : null;
        
        $article
            ->setKeyword($keyword)
            ->setAuthor($user)
            ->setSizeFrom($sizeFrom)
            ->setSizeTo($sizeTo)
        ;
        
        if($words) {
            foreach($words as $wordRaw) {
                if($wordRaw['word'] && $wordRaw['count']) {
                    $word = (new Word())
                                ->setWord($wordRaw['word'])
                                ->setCount($wordRaw['count'])    
                            ;
                    $article->addWord($word);
                    $word->setArticle($article);
                    $em->persist($word);
                }
            }
        }

        $slugger = new AsciiSlugger();

        if($title) {
            $article->setSlug($slugger->slug($title . '_' . uniqid()));
        } elseif($theme) {
            $article->setSlug($slugger->slug($theme->getTitle($keyword))->toString(). '_' . uniqid());
        } else {
            $article->setSlug($slugger->slug(uniqid()));
        }

        if($sizeFrom && $sizeTo) {
            $size = rand($sizeFrom, $sizeTo);
        } elseif($sizeFrom) {
            $size = $sizeFrom;
        } elseif($sizeTo) {
            $size = $sizeTo;
        } else {
            $size = null;
        }
        
        if($theme) {
            $article
                ->setBody($theme->getParagraphs($keyword))
                ->setTitle($theme->getTitle($keyword))
                ->setTheme($theme->getSlug())
            ;
        } else {
            $article
                ->setBody($this->contentProvider->getBody($article, $user, $article->getWords(), $size ?: self::$defaultArticleLength))
                ->setTitle($this->contentProvider->getTitle(($title) ? $title : '', ($keyword) ? $keyword : new Keyword()))
            ;  
        }
    }
}
