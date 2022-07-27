<?php

namespace App\Service;

use DateTime;
use App\Entity\User;
use App\Repository\SubscriptionRepository;

class BlaBlaArticleSubscriptionProvider
{
    private $subscriptions;

    private $subscriptionRepository;

    public function __construct(iterable $subscriptions, SubscriptionRepository $subscriptionRepository)
    {
        $this->subscriptions = $subscriptions;
        $this->subscriptionRepository = $subscriptionRepository;
    }

    public function getSubscriptionByLevel(int $level)
    {
        foreach($this->subscriptions as $subscription) {
            if($subscription->getLevel() == $level) {
                
                return $subscription;
            }
        }

        return null;
    }

    public function getSubscriptionByUser(User $user)
    {
        $subscriptionsInDB = $this->subscriptionRepository->findBy(['user' => $user->getId()], ['level' => 'DESC']);
        
        foreach($subscriptionsInDB as $subscription) {
            if(count($this->subscriptions)) {
                if($subscription->getCreatedAt() > (new DateTime('now'))->sub($this->getSubscriptionByLevel($subscription->getLevel())->getDuration())) {
                    
                    return $this->getSubscriptionByLevel($subscription->getLevel());
                }
                
            }
        }

        return null;
    }

    /**
     * Get the value of subscriptions
     */ 
    public function getSubscriptions()
    {
        return $this->subscriptions;
    }
}
