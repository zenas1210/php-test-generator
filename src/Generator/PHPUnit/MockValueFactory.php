<?php

namespace Zenas\PHPTestGenerator\Generator\PHPUnit;

use PhpParser\BuilderFactory;
use PhpParser\Node\Expr\MethodCall;
use Zenas\PHPTestGenerator\Resolver\ShortClassNameResolver;

class MockValueFactory implements ValueFactoryInterface
{
    private BuilderFactory $factory;
    private ShortClassNameResolver $shortClassNameProvider;

    public function __construct(BuilderFactory $factory, ShortClassNameResolver $shortClassNameProvider)
    {
        $this->factory = $factory;
        $this->shortClassNameProvider = $shortClassNameProvider;
    }

    public function getValueForType(?string $type): MethodCall
    {
        $name = $this->shortClassNameProvider->resolve($type);

        return $this->factory->methodCall(
            $this->factory->var('this'),
            'createMock',
            [
                $this->factory->classConstFetch($name, 'class'),
            ]
        );
    }

    public function supports(?string $type): bool
    {
        return $type !== null && (class_exists($type) || interface_exists($type));
    }
}