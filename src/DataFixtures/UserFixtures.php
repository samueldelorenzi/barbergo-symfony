<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setName('admin');
        $admin->setEmail('admin@barbergo.com.br');
        $admin->setRole('admin');
        $admin->setActive(true);

        $hashedPassword = $this->passwordHasher->hashPassword($admin, 'admin');
        $admin->setPassword($hashedPassword);

        $admin->setCreatedAt(new \DateTimeImmutable());
        $admin->setUpdatedAt(new \DateTimeImmutable());

        $manager->persist($admin);
        $manager->flush();
    }
}
