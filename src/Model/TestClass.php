<?php

namespace Zenas\PHPTestGenerator\Model;

use PhpParser\BuilderFactory;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Use_;

class TestClass
{
    /** @var Method[] */
    private $methods = [];

    /** @var Use_[] */
    private $uses = [];

    /** @var string */
    private $testPropertyName;

    /** @var ClassModel */
    private $class;

    /** @var string */
    private $shortName;

    /** @var string */
    private $namespace;

    public function __construct(ClassModel $class)
    {
        $this->class = $class;
        $this->testPropertyName = lcfirst($class->getReflection()->getShortName());
    }

    public function getTestPropertyName(): string
    {
        return $this->testPropertyName;
    }

    public function getClass(): ClassModel
    {
        return $this->class;
    }

    public function getShortName(): string
    {
        return $this->shortName;
    }

    public function setShortName(string $shortName): TestClass
    {
        $this->shortName = $shortName;

        return $this;
    }

    public function getMethods(): array
    {
        return $this->methods;
    }

    public function getMethod(string $name): Method
    {
        return $this->methods[$name];
    }

    public function setMethods(array $methods): self
    {
        $this->methods = $methods;

        return $this;
    }

    public function addMethod(Method $method): self
    {
        $this->methods[$method->getReflection()->getName()] = $method;

        return $this;
    }

    public function getUses(): array
    {
        return $this->uses;
    }

    public function setUses(array $use): self
    {
        $this->uses = $use;

        return $this;
    }

    public function getAlias(string $class): ?string
    {
        $alias = $this->uses[$class]->uses[0]->alias ?? null;

        if ($alias instanceof Identifier) {
            return $alias->name;
        }

        return $alias;
    }

    public function addUse(string $statement): void
    {
        if (!array_key_exists($statement, $this->uses)) {
            $this->uses[$statement] = (new BuilderFactory())->use($statement);
        }
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function setNamespace(string $namespace): self
    {
        $this->namespace = $namespace;

        return $this;
    }

    public function getFQCN(): string
    {
        return $this->namespace . '\\' . $this->shortName;
    }
}
