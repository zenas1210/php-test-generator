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
            ->addArgument('classes', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'Classes to generate tests for.')
            ->addOption('namespace', 's', InputOption::VALUE_OPTIONAL, 'Root namespace of application classes', $defaultNamespace)
            ->addOption('test-namespace', 't', InputOption::VALUE_OPTIONAL, 'Root namespace of test classes', $defaultTestNamespace)
            ->addOption('test-dir', 'd', InputOption::VALUE_OPTIONAL, 'Root directory of test classes', $defaultTestDir)
            ->addOption('overwrite', 'f', InputOption::VALUE_NONE, 'Overwrite existing test file/files')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $namespace = (string) $input->getOption('namespace');
        $testNamespace = (string) $input->getOption('test-namespace');
        $testDirectory = (string) $input->getOption('test-dir');
        $overwrite = $input->getOption('overwrite');

        $configuration = new Configuration($namespace, $testNamespace, $testDirectory);

        foreach ($input->getArgument('classes') as $class) {
            $output->writeln(sprintf('Generating test class for <info>%s</info>', $class));
            $output->writeln('');

            $generatedTestClass = $this->analyzer->generate($configuration, $class);
            $writePath = $this->writer->write($generatedTestClass, $configuration, $overwrite);

            $output->writeln(sprintf('Test class written to <info>%s</info>', $writePath));
        }

        return 0;
    }
}
