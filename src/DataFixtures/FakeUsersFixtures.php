<?php

namespace App\DataFixtures;

use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

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
        for ($i = 0; $i < 145; $i++) {
            $user = new Users();
            $user->setName($faker->name());
            $user->setEmail($faker->unique()->safeEmail());
            $user->setRole('user');
            $user->setActive($faker->boolean(70));

            $password = '123';
            $user->setPassword($this->passwordHasher->hashPassword($user, $password));

            $user->setCreatedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 year', 'now')));
            $user->setUpdatedAt(new \DateTimeImmutable());

            $manager->persist($user);
        }

        $manager->flush();
    }
}
