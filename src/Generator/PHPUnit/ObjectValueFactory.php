<?php

namespace Zenas\PHPTestGenerator\Generator\PHPUnit;

use PhpParser\BuilderFactory;
use PhpParser\Node\Expr\New_;
use ReflectionClass;
use Zenas\PHPTestGenerator\Resolver\ConstructorResolver;
use Zenas\PHPTestGenerator\Resolver\ShortClassNameResolver;

class ObjectValueFactory implements ValueFactoryInterface
{
    /** @var MethodArgumentsFactory */
    private $methodArgumentsFactory;

    /** @var BuilderFactory */
    private $factory;

    /** @var ShortClassNameResolver */
    private $shortClassNameProvider;

    /** @var ConstructorResolver */
    private $constructorResolver;

    public function __construct(
        MethodArgumentsFactory $methodArgumentsFactory,
        BuilderFactory         $factory,
        ShortClassNameResolver $shortClassNameProvider,
        ConstructorResolver $constructorResolver
    ) {
        $this->methodArgumentsFactory = $methodArgumentsFactory;
        $this->factory = $factory;
        $this->shortClassNameProvider = $shortClassNameProvider;
        $this->constructorResolver = $constructorResolver;
    }

    public function getValueForType(?string $type): New_
    {
        $reflection = new ReflectionClass($type);
        $constructor = $this->constructorResolver->resolve($reflection);
        $arguments = $constructor ? $this->methodArgumentsFactory->getArgumentsForMethod($constructor) : [];
        $name = $this->shortClassNameProvider->resolve($type);

        return $this->factory->new($name, array_values($arguments));
    }

    public function supports(?string $type): bool
    {
        if ($type === null || (!class_exists($type) && !interface_exists($type))) {
            return false;
        }

        $reflection = new ReflectionClass($type);

        return !$reflection->isInterface() && !$reflection->isAbstract();
    }
}
