<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\ApiToken;
use App\Entity\Subscription;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends BaseFixtures
{
    private $passwordEncoder;
    
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }
    
    public function loadData(ObjectManager $manager)
    {
        $this->create(User::class, function (User $user) use ($manager) {
            $user
                ->setfirstName('admin')
                ->setEmail('admin@blablaarticle.ru')
                ->setPassword($this->passwordEncoder->encodePassword($user, '123456'))
                ->setRoles(['ROLE_ADMIN']);
            ;
            $manager->persist(new ApiToken($user));
            $manager->persist(new Subscription($user, rand(1, 3)));
        });
        $this->create(User::class, function (User $user) use ($manager) {
            $user
                ->setfirstName('non_auth_user')
                ->setEmail('non_auth_user@blablaarticle.ru')
                ->setPassword($this->passwordEncoder->encodePassword($user, '123456'))
            ;
            $manager->persist(new Subscription($user, rand(1, 3)));
        });
        $this->createMany(User::class, 10, function (User $user) use ($manager) {
            $user
                ->setfirstName($this->faker->firstName())
                ->setEmail($this->faker->email)
                ->setPassword($this->passwordEncoder->encodePassword($user, '123456'));
            ;
            $manager->persist(new ApiToken($user));
            $manager->persist(new Subscription($user, rand(1, 3)));
        });

        $manager->flush();
    }
}
