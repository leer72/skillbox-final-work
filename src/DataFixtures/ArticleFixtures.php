<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Article;
use App\Entity\Keyword;
use App\Entity\Word;
use App\Service\ArticleContentProvider;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ArticleFixtures extends BaseFixtures implements DependentFixtureInterface
{

    private $contentProvider;

    public function __construct(ArticleContentProvider $contentProvider)
    {
        $this->contentProvider = $contentProvider;
    }

    public function loadData(ObjectManager $manager)
    {
        $this->createMany(Article::class, 10, function (Article $article) {
            // Для обеспечения уникальности при связи OneToOne используем массив уже связанных элементов
            $keyword = $this->getRandomReference(Keyword::class);
            while($this->inPool($keyword)) {
                $keyword = $this->getRandomReference(Keyword::class);  
            }
            
            $this->pool[] = $keyword;
            
            for($i = 1; $i < 3; $i++) {
                $word = $this->getRandomReference(Word::class);
                $article->addWord($word);
            }
            $words = $article->getWords();

            $sizeFrom = $sizeTo = rand(1, 5);
            $title = $this->contentProvider->getTitle('Покупайте наш {{ keyword }}', $keyword);
            $content = $this->contentProvider->getBody($keyword, $words, $sizeFrom);

            $article
                ->setTitle($title)
                ->setBody($content)
                ->setKeyword($keyword)
                ->setAuthor($this->getRandomReference(User::class))
                ->setSlug($this->faker->slug())
                ->setSizeFrom($sizeFrom)
                ->getSizeTo($sizeTo)
            ;
        });

       $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
            KeywordFixtures::class,
            WordsFixtures::class,
        ];
    }
}
