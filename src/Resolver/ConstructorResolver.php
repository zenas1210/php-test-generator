<?php

declare(strict_types=1);

namespace Zenas\PHPTestGenerator\Resolver;

use ReflectionClass;
use ReflectionMethod;

class ConstructorResolver
{
    public function resolve(ReflectionClass $class): ?ReflectionMethod
    {
        $currentClass = $class;

        do {
            $constructor = $currentClass->getConstructor();
            $currentClass = $currentClass->getParentClass();
        } while ($constructor === null && $currentClass !== false);

        return $constructor;
    }
}
