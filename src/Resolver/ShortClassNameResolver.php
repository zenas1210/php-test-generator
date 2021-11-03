<?php

declare(strict_types=1);

namespace Zenas\PHPTestGenerator\Resolver;

use Zenas\PHPTestGenerator\Model\TestClass;

class ShortClassNameResolver
{
    public TestClass $class;

    public function setClass(TestClass $class): void
    {
        $this->class = $class;
    }

    public function resolve(string $name): string
    {
        $alias = $this->class->getAlias($name);
        if ($alias !== null) {
            return $alias;
        }

        if (strpos($name, '\\') !== false) {
            $this->class->addUse($name);
            $parts = explode('\\', $name);

            return end($parts);
        }

        $fullName = $this->class->getClass()->getReflection()->getNamespaceName() . '\\' . $name;
        if (!(class_exists($name) || interface_exists($name)) && (class_exists($fullName) || interface_exists($fullName))) {
            $this->class->addUse($fullName);
        }

        return $name;
    }
}
