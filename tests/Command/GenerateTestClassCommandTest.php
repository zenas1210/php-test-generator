<?php

declare(strict_types=1);

namespace Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Tests\Fixtures\Dependency;
use Tests\Fixtures\TestedService;
use Tests\KernelTestCase;

class GenerateTestClassCommandTest extends KernelTestCase
{
    public function testExecute(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('generate-test-class');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'classes' => [TestedService::class, Dependency::class],
            '-d' => 'tests/output',
        ]);

        self::assertFileEquals('tests/Fixtures/ExpectedTestedServiceTest.txt', 'tests/output/Fixtures/TestedServiceTest.php');
        self::assertFileEquals('tests/Fixtures/ExpectedDependencyTest.txt', 'tests/output/Fixtures/DependencyTest.php');
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove('tests/output');
    }
}
