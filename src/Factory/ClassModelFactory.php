<?php

declare(strict_types=1);

namespace Zenas\PHPTestGenerator\Factory;

use DeepCopy\Reflection\ReflectionHelper;
use ReflectionClass;
use Zenas\PHPTestGenerator\Container\AstContainer;
use Zenas\PHPTestGenerator\Model\ClassModel;

class ClassModelFactory
{
    /** @var PropertyFactory */
    private $propertyMetadataFactory;

    /** @var PropertyAssignmentMapFactory */
    private $propertyMapFactory;

    /** @var ParameterFactory */
    private $parameterFactory;

    public function __construct(
        PropertyFactory               $propertyMetadataFactory,
        PropertyAssignmentMapFactory $propertyMapFactory,
        ParameterFactory $parameterFactory
    ) {
        $this->propertyMetadataFactory = $propertyMetadataFactory;
        $this->propertyMapFactory = $propertyMapFactory;
        $this->parameterFactory = $parameterFactory;
    }

    public function create(ReflectionClass $class, AstContainer $astContainer): ClassModel
    {
        $metadata = (new ClassModel())
            ->setReflection($class);

        $constructor = $class->getConstructor();
        $constructorParameters = [];
        if ($constructor !== null) {
            $propertyMap = $this->propertyMapFactory->getMap($astContainer, $class, '__construct');

            foreach ($constructor->getParameters() as $parameter) {
                $propertyName = $propertyMap[$parameter->getName()] ?? $parameter->getName();
                $constructorParameters[$propertyName] = $this->parameterFactory->create($parameter);
            }
        }

        $properties = [];
        foreach (ReflectionHelper::getProperties($class) as $name => $property) {
            $properties[$name] = $this->propertyMetadataFactory->create($property, $constructorParameters);
        }

        foreach ($constructorParameters as $name => $parameter) {
            if ($parameter->getType() === null) {
                $property = $properties[$name] ?? null;
                if ($property) {
                    $parameter->setType($property->getType());
                }
            }
        }

        $metadata->setProperties($properties);
        $metadata->setConstructorParameters($constructorParameters);

        return $metadata;
    }
}
