<?php
declare(strict_types=1);

namespace Zenas\PHPTestGenerator\Model;

class MethodGenerationContext
{
    /** @var Method */
    private $method;

    /** @var array */
    private $arguments;

    /** @var bool */
    private $dataProviders;

    public function __construct(Method $method, array $arguments, bool $dataProviders)
    {
        $this->method = $method;
        $this->arguments = $arguments;
        $this->dataProviders = $dataProviders;
    }

    public function getMethod(): Method
    {
        return $this->method;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function hasDataProviders(): bool
    {
        return $this->dataProviders;
    }
}
