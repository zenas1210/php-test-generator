<?php

declare(strict_types=1);

namespace Zenas\PHPTestGenerator\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zenas\PHPTestGenerator\Configuration\Configuration;
use Zenas\PHPTestGenerator\DependencyInjection\ContainerProvider;

class GenerateTestClassCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('generate-test-class')
            ->setDescription('Generate a PHPUnit test class from a class.')
            ->addArgument('class', InputArgument::REQUIRED, 'The class name to generate the test for.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $className = (string)$input->getArgument('class');

        $output->writeln(sprintf('Generating test class for <info>%s</info>', $className));
        $output->writeln('');

        $configuration = new Configuration('App', 'App\\Tests', 'tests');

        $container = ContainerProvider::get();
        $testClassGenerator = $container->get('php_test_generator.analyzer.class');
        $writer = $container->get('php_test_generator.writer.test_class');

        $generatedTestClass = $testClassGenerator->generate($configuration, $className);

        $writePath = $writer->write($generatedTestClass, $configuration);

        $output->writeln(sprintf('Test class written to <info>%s</info>', $writePath));

        return 0;
    }
}
