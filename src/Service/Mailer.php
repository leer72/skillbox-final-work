<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\SubscriptionRepository;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class Mailer 
{
    private $mailer;

    private $subscriptionProvider;

    private $subscriptionRepository;

    public function __construct(
        MailerInterface $mailerInterface, 
        BlaBlaArticleSubscriptionProvider $subscriptionProvider, 
        SubscriptionRepository $subscriptionRepository
    ) {
        $this->mailer = $mailerInterface;
        $this->subscriptionProvider = $subscriptionProvider;
        $this->subscriptionRepository = $subscriptionRepository;
    }

    public function sendSubscriptionMail(User $user)
    {
        $this->send('email/subscription_email.html.twig', 'Subscription', $user);
    }

    private function send(string $template, string $subject, User $user)
    {
        $subscription = $this->subscriptionProvider->getSubscriptionByUser($user);

        $subscriptionName = $subscription->getName();

        $subscriptionInDB = $this->subscriptionRepository->findOneBy(
            [
                'user' => $user->getId(), 
                'level' => $subscription->getLevel()
            ], 
            [
                'createdAt' => 'DESC'
            ]
        );
        
        $email = (new TemplatedEmail())
                ->subject($subject)
                ->htmlTemplate($template)
                ->context([
                    'subscriptionName' => $subscriptionName,
                    'subscriptionInDB' => (($subscriptionInDB->getcreatedAt())->add($subscription->getDuration()))->format('d.m.Y'),
                ])
        ;

        $email->to(new Address($user->getEmail(), $user->getFirstName()));
        
        $this->mailer->send($email);
    }
}
