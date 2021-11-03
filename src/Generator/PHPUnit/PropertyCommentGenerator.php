<?php

declare(strict_types=1);

namespace Zenas\PHPTestGenerator\Generator\PHPUnit;

use Symfony\Component\PropertyInfo\Type;
use Zenas\PHPTestGenerator\Resolver\ShortClassNameResolver;

class PropertyCommentGenerator
{
    private ShortClassNameResolver $shortClassNameProvider;

    public function __construct(ShortClassNameResolver $shortClassNameProvider)
    {
        $this->shortClassNameProvider = $shortClassNameProvider;
    }

    public function generateComment(Type $type): string
    {
        $types = [$type->getBuiltinType()];
        if ($type->getBuiltinType() === 'object') {
            $types = [
                $this->shortClassNameProvider->resolve($type->getClassName()),
                'MockObject',
            ];
        }

        return sprintf('/** @var %s */', implode('|', $types));
    }
}
