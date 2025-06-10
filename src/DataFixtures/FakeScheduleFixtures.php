<?php

namespace App\DataFixtures;

use App\Entity\BarberSchedule;
use App\Entity\Schedule;
use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class FakeScheduleFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('pt_BR');

        // Supondo que os barbeiros estão referenciados como 'user_0', 'user_1', ..., 'user_9'
        for ($i = 0; $i < 10; $i++) {
            $userRepository = $manager->getRepository(Users::class);
            $barbers = $userRepository->findBy(['role' => 'barber']);
            $barber = $barbers[$i] ?? null;

            // Criar horários para todos os dias da semana
            for ($day = 1; $day <= 6; $day++) { // Segunda a Sábado
                $schedule = new Schedule();

                $startHour = $faker->numberBetween(8, 10); // Ex: das 8h às 17h
                $endHour = $faker->numberBetween(17, 20);

                $schedule->setIdBarber($barber);
                $schedule->setWeekDay($day);
                $schedule->setStartTime(new \DateTime("$startHour:00"));
                $schedule->setEndTime(new \DateTime("$endHour:00"));

                $manager->persist($schedule);
            }
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
