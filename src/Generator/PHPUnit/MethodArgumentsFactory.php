<?php

namespace Zenas\PHPTestGenerator\Generator\PHPUnit;

use ReflectionMethod;

class MethodArgumentsFactory
{
    private ValueFactoryInterface $valueFactory;

    public function __construct(ValueFactoryInterface $valueFactory)
    {
        $this->valueFactory = $valueFactory;
    }

    public function getArgumentsForMethod(ReflectionMethod $method): array
    {
        $arguments = [];

        foreach ($method->getParameters() as $parameter) {
            if ($parameter->isDefaultValueAvailable()) {
                continue;
            }

            $arguments[$parameter->getName()] = null;

            $type = $parameter->getType();
            if ($type && !$type->allowsNull()) {
                $arguments[$parameter->getName()] = $this->valueFactory->getValueForType($type->getName());
            }
        }

        return $arguments;
    }
}