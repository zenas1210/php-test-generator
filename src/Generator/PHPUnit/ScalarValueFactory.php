<?php

namespace Zenas\PHPTestGenerator\Generator\PHPUnit;

class ScalarValueFactory implements ValueFactoryInterface
{
    private const VALUES = [
        'string' => 'some string',
        'int' => 2,
        'float' => 3.1,
        'bool' => true,
        'array' => [],
        'iterable' => [],
    ];

    public function getValueForType(?string $type)
    {
        return self::VALUES[$type] ?? null;
    }

    public function supports(?string $type): bool
    {
        return $type !== null && !class_exists($type) && !interface_exists($type);
    }
}
