<?php

namespace App\DataFixtures;

use App\Entity\Word;
use Doctrine\Persistence\ObjectManager;

class WordsFixtures extends BaseFixtures
{

    public function loadData(ObjectManager $manager)
    {
        $this->createMany(Word::class, 30, function (Word $word) {
            $word
                ->setWord($this->faker->word())
                ->setCount($this->faker->numberBetween(0, 10))
            ;
        });

       $manager->flush();
    }
}
