<?php

declare(strict_types=1);

namespace Zenas\PHPTestGenerator\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zenas\PHPTestGenerator\Analyzer\ClassAnalyzer;
use Zenas\PHPTestGenerator\Configuration\Configuration;
use Zenas\PHPTestGenerator\Writer\TestClassWriter;

class GenerateTestClassCommand extends Command
{
    /** @var ClassAnalyzer */
    private $analyzer;

    /** @var TestClassWriter */
    private $writer;

    public function __construct(ClassAnalyzer $analyzer, TestClassWriter $writer)
    {
        parent::__construct();

        $this->analyzer = $analyzer;
        $this->writer = $writer;
    }

    protected function configure(): void
    {
        $composerConfig = json_decode(file_get_contents('composer.json'), true);

        $autoloadConfig = $composerConfig['autoload']['psr-4'] ?? [];
        $autoloadDevConfig = $composerConfig['autoload-dev']['psr-4'] ?? [];

        $autoloadPsr4 = array_keys($autoloadConfig);
        $autoloadDevPsr4 = array_keys($autoloadDevConfig);

        $namespace = reset($autoloadPsr4);
        $testNamespace = reset($autoloadDevPsr4);

        $defaultNamespace = false === $namespace ? 'App' : trim($namespace, '\\');
        $defaultTestNamespace = false === $testNamespace ? 'App\\Tests' : trim($testNamespace, '\\');
        $defaultTestDir = false === $testNamespace ? 'tests' : trim($autoloadDevConfig[$testNamespace], '/');

        $this
            ->setName('generate-test-class')
            ->setDescription('Generate a PHPUnit test class from a class.')
            ->addArgument('class', InputArgument::REQUIRED, 'The class name to generate the test for.')
            ->addOption('namespace', 's', InputOption::VALUE_OPTIONAL, 'Root namespace of application classes', $defaultNamespace)
            ->addOption('test-namespace', 't', InputOption::VALUE_OPTIONAL, 'Root namespace of test classes', $defaultTestNamespace)
            ->addOption('test-dir', 'd', InputOption::VALUE_OPTIONAL, 'Root directory of test classes', $defaultTestDir)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $className = (string) $input->getArgument('class');

        $namespace = (string) $input->getOption('namespace');
        $testNamespace = (string) $input->getOption('test-namespace');
        $testDirectory = (string) $input->getOption('test-dir');

        $output->writeln(sprintf('Generating test class for <info>%s</info>', $className));
        $output->writeln('');

        $configuration = new Configuration($namespace, $testNamespace, $testDirectory);

        $generatedTestClass = $this->analyzer->generate($configuration, $className);

        $writePath = $this->writer->write($generatedTestClass, $configuration);

        $output->writeln(sprintf('Test class written to <info>%s</info>', $writePath));

        return 0;
    }
}
