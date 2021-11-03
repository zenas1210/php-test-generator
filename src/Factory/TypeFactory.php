<?php

namespace Zenas\PHPTestGenerator\Factory;

use ReflectionNamedType;
use Symfony\Component\PropertyInfo\Type;

class TypeFactory
{
    public function createFromReflectionNamedType(ReflectionNamedType $reflection): Type
    {
        $typeName = $reflection->isBuiltin() ? $reflection->getName() : Type::BUILTIN_TYPE_OBJECT;
        $class = $reflection->isBuiltin() ? null : $reflection->getName();

        return new Type($typeName, $reflection->allowsNull(), $class);
    }
}
