<?php

namespace App\DataFixtures;

use App\Entity\Appointments;
use App\Entity\Service;
use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class FakeAppointmentFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('pt_BR');

        $userRepository = $manager->getRepository(Users::class);
        $serviceRepository = $manager->getRepository(Service::class);

        $clients = $userRepository->findBy(['role' => 'user']);
        $barbers = $userRepository->findBy(['role' => 'barber']);
        $services = $serviceRepository->findAll();

        for ($i = 0; $i < 30; $i++) {
            $appointment = new Appointments();

            $client = $faker->randomElement($clients);
            $barber = $faker->randomElement($barbers);
            $service = $faker->randomElement($services);

            $appointmentDatetime = $faker->dateTimeBetween('+1 day', '+30 days');

            $appointment->setIdClient($client);
            $appointment->setIdBarber($barber);
            $appointment->setIdService($service);
            $appointment->setAppointmentDatetime($appointmentDatetime);
            $appointment->setStatus($faker->randomElement(['pendente', 'confirmado', 'cancelado']));
            $appointment->setCreatedAt(new \DateTime());
            $appointment->setUpdatedAt(new \DateTime());

            $manager->persist($appointment);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            FakeUsersFixtures::class,
            FakeServiceFixtures::class,
        ];
    }
}
