<?php

declare(strict_types=1);

namespace Zenas\PHPTestGenerator\Model;

class Mock
{
    /** @var string */
    private $dependecy;

    /** @var string */
    private $method;

    /** @var string|null */
    private $variableType;

    public function __construct(string $dependecy, string $method, ?string $variableType = null)
    {
        $this->dependecy = $dependecy;
        $this->method = $method;
        $this->variableType = $variableType;
    }

    public function getDependecy(): string
    {
        return $this->dependecy;
    }

    public function setDependecy(string $dependecy): self
    {
        $this->dependecy = $dependecy;

        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setMethod(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    public function getVariableType(): ?string
    {
        return $this->variableType;
    }

    public function setVariableType(?string $variableType): self
    {
        $this->variableType = $variableType;

        return $this;
    }
}
