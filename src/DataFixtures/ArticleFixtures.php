<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ArticleFixtures extends BaseFixtures implements DependentFixtureInterface
{
    private static $words = [
        'КОФЕ',
        'КАРЛСОН',
        'ЗЕЛЕНЫЙ',
        'ПОДНИМАТЬ',
        'АЗБУКА',
        'ТРИГГЕР',
        'ПРИЗРАК',
        'ЭХО',
        'СТРОКА',
        'САМОЛЕТ'
    ];

    public function loadData(ObjectManager $manager)
    {
        $this->createMany(Article::class, 25, function (Article $article) {
            $article->setTitle($this->faker->text($this->faker->numberBetween(20, 40)));
            
            $word = $this->faker->randomElement(self::$words);
            $content = $this->faker->text($this->faker->numberBetween(500, 800));
            $article->setKeywords($word);
            
            $article
                ->setAuthor($this->getRandomReference(User::class))
                ->setBody($content)
            ;
        });

       $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
    }
}
