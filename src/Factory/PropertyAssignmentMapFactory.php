<?php

declare(strict_types=1);

namespace Zenas\PHPTestGenerator\Factory;

use ReflectionClass;
use Zenas\PHPTestGenerator\Container\AstContainer;
use Zenas\PHPTestGenerator\Extractor\PropertyVariableAssignmentExtractor;
use Zenas\PHPTestGenerator\Extractor\StaticCallArgumentsExtractor;

class PropertyAssignmentMapFactory
{
    /** @var PropertyVariableAssignmentExtractor */
    private $propertyAssignmentExtractor;

    /** @var StaticCallArgumentsExtractor */
    private $staticCallArgumentsExtractor;

    public function __construct(
        PropertyVariableAssignmentExtractor $propertyAssignmentExtractor,
        StaticCallArgumentsExtractor        $staticCallArgumentsExtractor
    ) {
        $this->propertyAssignmentExtractor = $propertyAssignmentExtractor;
        $this->staticCallArgumentsExtractor = $staticCallArgumentsExtractor;
    }

    public function getMap(AstContainer $ast, ReflectionClass $class, string $methodName, array $map = []): array
    {
        if (!$ast->hasMethod($methodName)) {
            if (!$ast->getParentContainer()) {
                return $map;
            }

            return $this->getMap($ast->getParentContainer(), $class->getParentClass(), $methodName, $map);
        }

        $method = $class->getMethod($methodName);
        $parameters = $method->getParameters();

        if ($map === []) {
            foreach ($parameters as $i => $parameter) {
                $map[$parameter->getName()] = $i;
            }
        }

        $statements = $ast->getMethod($method->getName())->stmts;
        $assignments = $this->propertyAssignmentExtractor->extract($statements);
        $passedToParent = $this->staticCallArgumentsExtractor->extract($statements, 'parent', '__construct');

        $originalMap = $map;
        foreach ($parameters as $i => $parameter) {
            $name = $parameter->getName();
            $originalName = array_search($i, $originalMap);
            if ($originalName !== false && is_int($originalMap[$originalName])) {
                if (array_key_exists($name, $assignments)) {
                    $map[$originalName] = $assignments[$name];
                } elseif (array_key_exists($name, $passedToParent)) {
                    $map[$originalName] = $passedToParent[$name];
                }
            }
        }

        foreach ($map as $key => $value) {
            if (is_int($value)) {
                if ($class->getParentClass()) {
                    return $this->getMap($ast->getParentContainer(), $class->getParentClass(), $methodName, $map);
                }

                unset($map[$key]);
            }
        }

        return $map;
    }
}
