<?php

declare(strict_types=1);

namespace Zenas\PHPTestGenerator\Writer;

use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Zenas\PHPTestGenerator\Configuration\Configuration;
use Zenas\PHPTestGenerator\Model\GeneratedTestClass;

class TestClassWriter
{
    /** @var Filesystem */
    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function write(GeneratedTestClass $generatedTestClass, Configuration $configuration): string
    {
        $writePath = $this->getWritePath($generatedTestClass, $configuration);

        $writeDirectory = dirname($writePath);

        if (!$this->filesystem->exists($writeDirectory)) {
            $this->filesystem->mkdir($writeDirectory);
        }

        if ($this->filesystem->exists($writePath)) {
            throw new RuntimeException(sprintf('Test class already exists at %s', $writePath));
        }

        $this->filesystem->dumpFile($writePath, $generatedTestClass->getCode());

        return $writePath;
    }

    private function getWritePath(GeneratedTestClass $generatedTestClass, Configuration $configuration): string
    {
        $writePath = $configuration->getTestsDir() . '/' . str_replace(
            $configuration->getTestsNamespace() . '\\',
            '',
            $generatedTestClass->getTestClassName()
        ) . '.php';

        return str_replace('\\', DIRECTORY_SEPARATOR, $writePath);
    }
}
