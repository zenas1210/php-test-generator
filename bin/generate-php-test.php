<?php

declare(strict_types=1);

namespace Zenas\PHPTestGenerator;

use Zenas\PHPTestGenerator\Command\GenerateTestClassCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use const PHP_EOL;
use function array_merge;
use function file_exists;
use function getcwd;

(static function () : void {
    $autoloadFiles = [
        getcwd() . '/vendor/autoload.php',
        __DIR__ . '/../vendor/autoload.php',
        __DIR__ . '/../../../autoload.php',
    ];

    $autoloaderFound = false;

    foreach ($autoloadFiles as $autoloadFile) {
        if (! file_exists($autoloadFile)) {
            continue;
        }

        require_once $autoloadFile;
        $autoloaderFound = true;
    }

    if (! $autoloaderFound) {
        echo 'vendor/autoload.php could not be found. Did you run `composer install`?', PHP_EOL;
        exit(1);
    }

    $argv = $_SERVER['argv'];

    if (! isset($argv[1]) || $argv[1] !== 'generate-test-class') {
        $base = $argv[0];
        unset($argv[0]);
        $argv = array_merge([$base], ['generate-test-class'], $argv);
    }

    $input = new ArgvInput($argv);

    $application = new Application();
    $application->add(new GenerateTestClassCommand());
    $application->run($input);
})();
