<?php

namespace App\DataFixtures;

use App\Entity\BarberBarbershop;
use App\Entity\Barbershop;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class FakeBarberBarbershopFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('pt_BR');

        for ($i = 0; $i < 5; $i++) {
            $barberBarbershop = new BarberBarbershop();
            $userRepository = $manager->getRepository(User::class);
            $barbershopRepository = $manager->getRepository(Barbershop::class);

            $barbers = $userRepository->findBy(['role' => 'barber']);
            $barber = $barbers[$i] ?? null;
            $barbershops = $barbershopRepository->findAll();
            $barbershop = $barbershops[$i] ?? null;

            $barberBarbershop->setIdBarber($barber);
            $barberBarbershop->setIdBarbershop($barbershop);

            $manager->persist($barberBarbershop);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            FakeUsersFixtures::class,
            FakeBarbershopFixtures::class,
        ];
    }
}
