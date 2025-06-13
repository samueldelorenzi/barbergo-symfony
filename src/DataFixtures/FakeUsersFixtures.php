<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class FakeUsersFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('pt_BR');

        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setName($faker->name());
            $user->setEmail($faker->unique()->safeEmail());
            $user->setRole('user');
            $user->setActive($faker->boolean(70));
            $user->setPassword($this->passwordHasher->hashPassword($user, '123'));
            $user->setCreatedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 year', 'now')));
            $user->setUpdatedAt(new \DateTimeImmutable());

            $manager->persist($user);
        }

        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setName($faker->name());
            $user->setEmail($faker->unique()->safeEmail());
            $user->setRole('barber');
            $user->setActive($faker->boolean(70));
            $user->setPassword($this->passwordHasher->hashPassword($user, '123'));
            $user->setCreatedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 year', 'now')));
            $user->setUpdatedAt(new \DateTimeImmutable());

            $manager->persist($user);
        }

        $manager->flush();
    }
}
