<?php

namespace App\DataFixtures;

use App\Entity\Barbershop;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class FakeBarbershopFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('pt_BR');

        $userRepository = $manager->getRepository(User::class);

        $creatorUsers = $userRepository->findBy(['role' => 'barber']);

        $barbershopsData = [
            ['Corte Fino', 'Belo Horizonte', 'Rua dos Andradas, 78', false, '2025-06-06 20:06:53'],
            ['Barbearia Central', 'São Paulo', 'Rua das Flores, 123', true, '2025-06-06 20:06:53'],
            ['Barber House', 'Rio de Janeiro', 'Av. Atlântica, 456', true, '2025-06-06 20:06:53'],
            ['Estilo Barbershop', 'Curitiba', 'Rua XV de Novembro, 321', true, '2025-06-06 20:06:53'],
            ['Elite Barber', 'Porto Alegre', 'Av. Ipiranga, 999', true, '2025-06-06 20:06:53'],
        ];

        $counter = 0;
        foreach ($barbershopsData as [$name, $city, $address, $active, $createdAt]) {
            $creatorUser = $creatorUsers[$counter] ?? null;
            $barbershop = new Barbershop();
            $barbershop->setCreatedBy($creatorUser);
            $barbershop->setName($name);
            $barbershop->setCity($city);
            $barbershop->setAddress($address);
            $barbershop->setActive($active);
            $barbershop->setCreatedAt(new \DateTimeImmutable($createdAt));

            $manager->persist($barbershop);

            $counter++;
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            FakeUsersFixtures::class,
        ];
    }
}
