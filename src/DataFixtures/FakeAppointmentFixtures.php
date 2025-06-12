<?php

namespace App\DataFixtures;

use App\Entity\Appointment;
use App\Entity\Service;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class FakeAppointmentFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('pt_BR');

        $userRepository = $manager->getRepository(User::class);
        $serviceRepository = $manager->getRepository(Service::class);

        $clients = $userRepository->findBy(['role' => 'user']);
        $barbers = $userRepository->findBy(['role' => 'barber']);
        $services = $serviceRepository->findAll();

        for ($i = 0; $i < 30; $i++) {
            $appointment = new Appointment();

            $client = $faker->randomElement($clients);
            $barber = $faker->randomElement($barbers);
            $service = $faker->randomElement($services);

            $datetime = $faker->dateTimeBetween('+1 day', '+30 days');
            $dateOnly = \DateTime::createFromFormat('Y-m-d', $datetime->format('Y-m-d'));
            $timeOnly = \DateTime::createFromFormat('H:i:s', $datetime->format('H:i:s'));

            $appointment->setIdClient($client);
            $appointment->setIdBarber($barber);
            $appointment->setIdService($service);
            $appointment->setAppointmentDate($dateOnly);
            $appointment->setAppointmentTime($timeOnly);
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
