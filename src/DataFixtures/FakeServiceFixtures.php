<?php

namespace App\DataFixtures;

use App\Entity\Barbershop;
use App\Entity\Service;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class FakeServiceFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('pt_BR');

        $barbershopRepository = $manager->getRepository(Barbershop::class);
        $barbershops = $barbershopRepository->findAll();

        foreach ($barbershops as $barbershop) {
            // Cada barbearia terá entre 2 e 5 serviços
            $serviceCount = $faker->numberBetween(2, 5);

            for ($i = 0; $i < $serviceCount; $i++) {
                $service = new Service();

                $service->setIdBarbershop($barbershop);
                $service->setName($faker->randomElement([
                    'Corte masculino',
                    'Barba completa',
                    'Sobrancelha',
                    'Corte + Barba',
                    'Hidratação capilar',
                    'Luzes ou coloração'
                ]));

                $service->setDurationMinutes($faker->numberBetween(20, 90));
                $service->setPrice($faker->randomFloat(2, 20, 150));
                $service->setCreatedAt(new \DateTime());

                $manager->persist($service);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            FakeBarbershopFixtures::class,
        ];
    }
}
