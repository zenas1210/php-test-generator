<?php
declare(strict_types=1);

namespace Tests\Command;

use Generator;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Tests\Fixtures\Dependency;
use Tests\Fixtures\TestedService;
use Tests\KernelTestCase;

class GenerateTestClassCommandTest extends KernelTestCase
{
    /**
     * @dataProvider executeProvider
     */
    public function testExecute(string $class, string $actualFile, string $expectedFile, array $options = []): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('generate-test-class');
        $commandTester = new CommandTester($command);
        $defaultOptions = [
            'classes' => [$class],
            '-d' => 'tests/output',
        ];
        $commandTester->execute(array_merge($defaultOptions, $options));

        self::assertFileEquals($expectedFile, $actualFile);
    }

    public function executeProvider(): Generator
    {
        yield [
            TestedService::class,
            'tests/output/Fixtures/TestedServiceTest.php',
            'tests/Fixtures/ExpectedTestedServiceTest.txt',
        ];

        yield [
            Dependency::class,
            'tests/output/Fixtures/DependencyTest.php',
            'tests/Fixtures/ExpectedDependencyTest.txt',
        ];

        yield [
            TestedService::class,
            'tests/output/Fixtures/TestedServiceTest.php',
            'tests/Fixtures/ExpectedTestedServiceTestWithProviders.txt',
            ['-p' => true],
        ];
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove('tests/output');
    }
}
