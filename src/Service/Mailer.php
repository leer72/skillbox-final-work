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
        $this->send(
            'email/subscription_email.html.twig', 
            'Subscription', 
            $user, 
            function (TemplatedEmail $email) use ($subscriptionInDB, $subscriptionName, $subscription) {
                $email
                ->context([
                    'subscriptionName' => $subscriptionName,
                    'subscriptionInDB' => (($subscriptionInDB->getcreatedAt())->add($subscription->getDuration()))->format('d.m.Y'),
                ])
                ;
            });
    }

    public function sendChangeEmail(User $user)
    {
        $this->send(
            'email/change_email.html.twig', 
            'Confirm email change', 
            $user->getNewEmail(), 
            function (TemplatedEmail $email) use ($user) {
            $email
            ->context([
                'newEmail' => $user->getNewEmail(),
                'token' => $user->getEmailToken(),
            ])
            ;
        });
    }

    private function send(string $template, string $subject, $recipient, \Closure $callback = null)
    {
        $email = (new TemplatedEmail())
                ->subject($subject)
                ->htmlTemplate($template)
        ;

        if ($recipient instanceof User) {
            $email->to(new Address($recipient->getEmail(), $recipient->getFirstName()));
        } elseif (is_string($recipient)) {
            $email->to(new Address($recipient));
        } else {
            new \Exception('Получатель неопределен');
        }

        if ($callback) {
            $callback($email);
        }

        $this->mailer->send($email);
    }
}
