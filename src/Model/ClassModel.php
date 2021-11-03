<?php

declare(strict_types=1);

namespace Zenas\PHPTestGenerator\Model;

use ReflectionClass;
use ReflectionMethod;

class ClassModel
{
    private ReflectionClass $reflection;

    /**
     * @var Property[]
     */
    private array $properties;

    /**
     * @var ReflectionMethod[]
     */
    private array $methods;

    /**
     * @var Parameter[]
     */
    private array $constructorParameters;

    public function getReflection(): ReflectionClass
    {
        return $this->reflection;
    }

    public function setReflection(ReflectionClass $reflection): self
    {
        $this->reflection = $reflection;

        return $this;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function setProperties(array $properties): self
    {
        $this->properties = $properties;

        return $this;
    }

    public function getProperty(string $name): Property
    {
        return $this->properties[$name];
    }

    public function getMethods(): array
    {
        return $this->methods;
    }

    public function setMethods(array $methods): self
    {
        $this->methods = $methods;

        return $this;
    }

    public function getConstructorParameters(): array
    {
        return $this->constructorParameters;
    }

    public function setConstructorParameters(array $constructorParameters): self
    {
        $this->constructorParameters = $constructorParameters;

        return $this;
    }
}
