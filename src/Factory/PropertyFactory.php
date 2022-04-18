<?php

declare(strict_types=1);

namespace Zenas\PHPTestGenerator\Factory;

use ReflectionParameter;
use ReflectionProperty;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Zenas\PHPTestGenerator\Model\Property;

class PropertyFactory
{
    /** @var PropertyInfoExtractor */
    private $extractor;

    /** @var TypeFactory */
    private $typeFactory;

    public function __construct(PropertyInfoExtractor $extractor, TypeFactory $typeFactory)
    {
        $this->extractor = $extractor;
        $this->typeFactory = $typeFactory;
    }

    public function create(ReflectionProperty $reflection, array $constructorParameters): Property
    {
        $property = (new Property())->setReflection($reflection);

        $type = $this->extractor->getTypes($reflection->getDeclaringClass()->name, $reflection->name)[0] ?? null;
        if (!$type) {
            /** @var ReflectionParameter|null $constructorParameter */
            $constructorParameter = $constructorParameters[$reflection->getName()] ?? null;
            if ($constructorParameter) {
                $reflectionType = $constructorParameter->getType();
                if ($reflectionType) {
                    $type = $this->typeFactory->createFromReflectionNamedType($reflectionType);
                }
            }
        }

        return $property->setType($type);
    }
}
