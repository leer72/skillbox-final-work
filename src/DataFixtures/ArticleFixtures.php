<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Article;
use App\Entity\Keyword;
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
            
            $words = array(
                $this->faker->word() => $this->faker->numberBetween(1, 7), 
                $this->faker->word() => $this->faker->numberBetween(1, 7), 
                $this->faker->word() => $this->faker->numberBetween(1, 7), 
                $this->faker->word() => $this->faker->numberBetween(1, 7),
            );
            
            $title = $this->contentProvider->getTitle('Покупайте наш {{ keyword }}', $keyword);
            $content = $this->contentProvider->getBody($keyword, $words, rand(1, 5));

            $article
                ->setTitle($title)
                ->setWords($words)
                ->setBody($content)
                ->setKeyword($keyword)
                ->setTheme($this->faker->text(20))
                ->setAuthor($this->getRandomReference(User::class))
            ;
        });

       $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
            KeywordFixtures::class,
        ];
    }
}
