<?php

declare(strict_types=1);

namespace Zenas\PHPTestGenerator\Configuration;

class Configuration
{
    private string $sourceNamespace;
    private string $testsNamespace;
    private string $testsDir;

    public function __construct(string $sourceNamespace, string $testsNamespace, string $testsDir)
    {
        $this->sourceNamespace = $sourceNamespace;
        $this->testsNamespace = $testsNamespace;
        $this->testsDir = $testsDir;
    }

    public function getSourceNamespace() : string
    {
        return $this->sourceNamespace;
    }

    public function getTestsNamespace() : string
    {
        return $this->testsNamespace;
    }

    public function getTestsDir() : string
    {
        return $this->testsDir;
    }
}
