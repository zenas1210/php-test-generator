<?php
declare(strict_types=1);

namespace Zenas\PHPTestGenerator\Factory;

use ReflectionProperty;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Zenas\PHPTestGenerator\Model\Parameter;
use Zenas\PHPTestGenerator\Model\Property;

class PropertyFactory
{
    /** @var PropertyInfoExtractor */
    private $extractor;

    public function __construct(PropertyInfoExtractor $extractor)
    {
        $this->extractor = $extractor;
    }

    /**
     * @param ReflectionProperty $reflection
     * @param Parameter[]        $constructorParameters
     *
     * @return Property
     */
    public function create(ReflectionProperty $reflection, array $constructorParameters): Property
    {
        $property = (new Property())->setReflection($reflection);

        $type = $this->extractor->getTypes($reflection->getDeclaringClass()->name, $reflection->name)[0] ?? null;
        if (!$type) {
            $constructorParameter = $constructorParameters[$reflection->getName()] ?? null;
            $type = $constructorParameter ? $constructorParameter->getType() : null;
        }

        return $property->setType($type);
    }
}
