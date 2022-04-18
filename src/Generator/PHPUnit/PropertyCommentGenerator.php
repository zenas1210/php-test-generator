<?php

declare(strict_types=1);

namespace Zenas\PHPTestGenerator\Generator\PHPUnit;

use ReflectionClass;
use Symfony\Component\PropertyInfo\Type;
use Zenas\PHPTestGenerator\Resolver\ShortClassNameResolver;

class PropertyCommentGenerator
{
    /** @var ShortClassNameResolver */
    private $shortClassNameProvider;

    public function __construct(ShortClassNameResolver $shortClassNameProvider)
    {
        $this->shortClassNameProvider = $shortClassNameProvider;
    }

    public function generateDependencyComment(Type $type): string
    {
        $types = [$type->getBuiltinType()];
        if ($type->getBuiltinType() === 'object') {
            $types = [
                $this->shortClassNameProvider->resolve($type->getClassName()),
                'MockObject',
            ];
        }

        return $this->generateComment($types);
    }

    public function generateClassComment(ReflectionClass $class)
    {
        return $this->generateComment([$class->getShortName()]);
    }

    public function generateComment(array $types)
    {
        return sprintf('/** @var %s */', implode('|', $types));
    }
}
