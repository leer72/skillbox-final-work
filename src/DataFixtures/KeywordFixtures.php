<?php

namespace App\DataFixtures;

use App\Entity\Keyword;
use Doctrine\Persistence\ObjectManager;

class KeywordFixtures extends BaseFixtures
{
    private static $words = [
        'КОФЕ',
        'КАРЛСОН',
        'АЗБУКА',
        'ТРИГГЕР',
        'ПРИЗРАК',
        'ЭХО',
        'СТРОКА',
        'САМОЛЕТ'
    ];
    
    public function loadData(ObjectManager $manager)
    {
        $this->createMany(Keyword::class, 10, function (Keyword $keyword) use ($manager) {
            // Пока не используем словоформы - оставляем только один элемент массива
            $word = $this->faker->randomElement(self::$words);
            $keyword
                ->setKeyword(array(
                    $word,
                    $word,
                    $word,
                    $word,
                    $word,
                    $word,
                    $word
                    ))
            ;
        });

        $manager->flush();
    }
}
