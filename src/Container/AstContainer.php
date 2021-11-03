<?php

declare(strict_types=1);

namespace Zenas\PHPTestGenerator\Container;

use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Use_;
use UnexpectedValueException;

class AstContainer
{
    /**
     * @var ClassMethod[]
     */
    private array $methods;

    /**
     * @var Use_[]
     */
    private array $uses;

    private ?AstContainer $parentContainer;

    public function getAlias(string $class): ?string
    {
        $alias = $this->uses[$class]->uses[0]->alias ?? null;

        if ($alias instanceof Identifier) {
            return $alias->name;
        }

        return $alias;
    }

    public function getMethod(string $name): ClassMethod
    {
        $method = $this->methods[$name] ?? null;
        if ($method !== null) {
            return $method;
        }

        throw new UnexpectedValueException("Couldn't find method $name");
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

    public function hasMethod(string $method): bool
    {
        return array_key_exists($method, $this->methods);
    }

    public function getUses(): array
    {
        return $this->uses;
    }

    public function setUses(array $uses): self
    {
        $this->uses = $uses;

        return $this;
    }

    public function getParentContainer(): ?AstContainer
    {
        return $this->parentContainer;
    }

    public function setParentContainer(?AstContainer $parentContainer): self
    {
        $this->parentContainer = $parentContainer;

        return $this;
    }
}
