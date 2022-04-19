<?php

declare(strict_types=1);

namespace Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
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
            'class' => TestedService::class,
            '-d' => 'tests/output',
        ]);

        $this->assertFileEquals('tests/Fixtures/ExpectedTestedServiceTest.txt', 'tests/output/Fixtures/TestedServiceTest.php');
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove('tests/output');
    }
}
