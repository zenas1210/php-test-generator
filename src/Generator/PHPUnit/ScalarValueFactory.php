<?php

namespace Zenas\PHPTestGenerator\Generator\PHPUnit;

use PhpParser\BuilderFactory;

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

    /** @var BuilderFactory */
    private $factory;

    public function __construct(BuilderFactory $factory)
    {
        $this->factory = $factory;
    }

    public function getValueForType(?string $type)
    {
        if ($type === 'object') {
            return $this->factory->new('\stdClass');
        }

        return self::VALUES[$type] ?? null;
    }

    public function supports(?string $type): bool
    {
        return $type !== null && !class_exists($type) && !interface_exists($type);
    }
}
