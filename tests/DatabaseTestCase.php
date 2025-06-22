<?php

namespace App\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

abstract class DatabaseTestCase extends WebTestCase
{
    protected static ?EntityManagerInterface $em = null;

    public static function setUpBeforeClass(): void
    {
        $kernel = new \App\Kernel('test', true);
        $kernel->boot();

        $application = new Application($kernel);
        $application->setAutoExit(false);

        $commands = [
            ['doctrine:database:drop', '--force' => true, '--if-exists' => true],
            ['doctrine:database:create'],
            ['doctrine:migrations:migrate', '--no-interaction' => true],
            ['doctrine:fixtures:load', '--no-interaction' => true],
        ];

        foreach ($commands as $cmd) {
            $command = array_shift($cmd);
            $options = $cmd;
            $input = new ArrayInput(array_merge(['command' => $command, '--env' => 'test'], $options));
            $application->run($input, new NullOutput());
        }

        self::$em = $kernel->getContainer()->get('doctrine')->getManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (self::$em !== null) {
            self::$em->clear();
        }
    }
}
