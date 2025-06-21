<?php

namespace App\DataFixtures;

use App\Entity\BarberSchedule;
use App\Entity\Schedule;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class FakeScheduleFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('pt_BR');

        for ($i = 0; $i < 10; $i++) {
            $userRepository = $manager->getRepository(User::class);
            $barbers = $userRepository->findBy(['role' => 'barber']);
            $barber = $barbers[$i] ?? null;

            for ($day = 0; $day <= 6; $day++) { // Domingo a segunda
                $schedule = new Schedule();

                $startHour = $faker->numberBetween(8, 10); // Ex: das 8h às 17h
                $endHour = $faker->numberBetween(17, 20);

                $schedule->setIdBarber($barber);
                $schedule->setActive($faker->boolean(70));
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
