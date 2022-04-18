<?php

namespace Zenas\PHPTestGenerator\Model;

use ReflectionMethod;

class Method
{
    /** @var Mock[] */
    private $mocks = [];

    /** @var array */
    private $arguments = [];

    /** @var array */
    private $exceptions = [];

    /** @var bool */
    private $hasReturn = false;

    /** @var ReflectionMethod */
    private $reflection;

    /** @var string|null */
    private $returnType;

    /** @var ClassModel */
    private $class;

    /** @var TestClass */
    private $testClass;

    /** @var array */
    private $methodCalls = [];

    /** @var Method|null */
    private $parent;

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function setArguments(array $arguments): self
    {
        $this->arguments = $arguments;

        return $this;
    }

    public function setArgument(string $name, $value): self
    {
        $this->arguments[$name] = $value;

        return $this;
    }

    public function getMocks(): array
    {
        return $this->mocks;
    }

    public function addMock(Mock $mock): self
    {
        $this->mocks[$mock->getDependecy() . '|' . $mock->getMethod()] = $mock;

        return $this;
    }

    public function getExceptions(): array
    {
        return array_unique($this->exceptions);
    }

    public function setExceptions(array $exceptions): self
    {
        $this->exceptions = $exceptions;

        return $this;
    }

    public function addException(string $class): self
    {
        $this->exceptions[] = $class;

        return $this;
    }

    public function hasReturn(): bool
    {
        return $this->hasReturn;
    }

    public function setHasReturn(bool $hasReturn): self
    {
        $this->hasReturn = $hasReturn;

        return $this;
    }

    public function getReflection(): ReflectionMethod
    {
        return $this->reflection;
    }

    public function setReflection(ReflectionMethod $reflection): self
    {
        $this->reflection = $reflection;

        $returnType = $reflection->getReturnType();
        if ($returnType !== null) {
            $this->returnType = $returnType->getName();
        }

        return $this;
    }

    public function getReturnType(): ?string
    {
        return $this->returnType;
    }

    public function setReturnType(?string $returnType): self
    {
        $this->returnType = $returnType;

        return $this;
    }

    public function getClass(): ClassModel
    {
        return $this->class;
    }

    public function setClass(ClassModel $class): self
    {
        $this->class = $class;

        return $this;
    }

    public function getMethodCalls(): array
    {
        return $this->methodCalls;
    }

    public function setMethodCalls(array $methodCalls): self
    {
        $this->methodCalls = $methodCalls;

        return $this;
    }

    public function addMethodCall(string $variable, string $name)
    {
        $key = "$variable::$name";
        if ($key !== "this::{$this->reflection->name}" && !in_array($name, $this->methodCalls)) {
            $this->methodCalls[] = $key;
        }
    }

    public function getTestClass(): TestClass
    {
        return $this->testClass;
    }

    public function setTestClass(TestClass $testClass): self
    {
        $this->testClass = $testClass;

        return $this;
    }

    public function isTestable(): bool
    {
        $name = $this->reflection->getName();

        return $this->reflection->isPublic() && ($name === '__invoke' || strpos($name, '__') !== 0);
    }

    public function getParent(): ?Method
    {
        return $this->parent;
    }

    public function setParent(?Method $parent): self
    {
        $this->parent = $parent;

        return $this;
    }
}
