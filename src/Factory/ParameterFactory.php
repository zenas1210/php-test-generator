<?php

namespace Zenas\PHPTestGenerator\Factory;

use ReflectionParameter;
use Zenas\PHPTestGenerator\Model\Parameter;

class ParameterFactory
{
    /** @var TypeFactory */
    private $typeFactory;

    public function __construct(TypeFactory $typeFactory)
    {
        $this->typeFactory = $typeFactory;
    }

    public function create(ReflectionParameter $reflection): Parameter
    {
        $parameter = (new Parameter())->setReflection($reflection);

        $reflectionType = $reflection->getType();
        if ($reflectionType) {
            $type = $this->typeFactory->createFromReflectionNamedType($reflectionType);
            $parameter->setType($type);
        }

        return $parameter;
    }
}
