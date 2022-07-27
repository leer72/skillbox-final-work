<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Subscription;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class SubscribtionFixtures extends BaseFixtures implements DependentFixtureInterface
{
    public function loadData(ObjectManager $manager)
    {
        $this->createMany(Subscription::class, 10, function (Subscription $subscription) use ($manager) {
            $user = $this->getRandomReference(User::class);
            $subscription
                ->setLevel(rand(1,3))
                ->setUser($user)
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
