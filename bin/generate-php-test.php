<?php

require __DIR__ . '/../vendor/autoload.php';
require getcwd() . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Zenas\PHPTestGenerator\Kernel;

$kernel = new Kernel('prod', false);
$kernel->boot();

(new Application())
    ->add($kernel->getContainer()->get('php_test_generator.command.generate_test_class'))
    ->getApplication()
    ->setDefaultCommand('generate-test-class', true)
    ->run();
