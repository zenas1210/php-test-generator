<?php

declare(strict_types=1);

namespace Zenas\PHPTestGenerator\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeVisitorAbstract;
use ReflectionMethod;
use Zenas\PHPTestGenerator\Model\Method;
use Zenas\PHPTestGenerator\Model\Mock;

class DependencyNodeVisitor extends NodeVisitorAbstract
{
    private Method $method;

    public function __construct(Method $method)
    {
        $this->method = $method;
    }

    public function leaveNode(Node $node): void
    {
        if ($node instanceof Expression) {
            $expr = $node->expr;
        } elseif ($node instanceof MethodCall) {
            $expr = $node;
        } else {
            return;
        }

        $propertyFetch = $expr->var ?? null;
        $variable = $propertyFetch->var ?? null;
        if (!$expr instanceof MethodCall
            || !$propertyFetch instanceof PropertyFetch
            || !$variable instanceof Variable
            || $variable->name !== 'this'
        ) {
            return;
        }

        $methodName = $expr->name->name;
        $property = $this->method->getClass()->getProperty($propertyFetch->name->name);
        $type = $property->getType();
        if ($type === null || $type->getClassName() === null) {
            return;
        }

        $methodReflection = new ReflectionMethod($type->getClassName(), $methodName);

        $returnType = $methodReflection->getReturnType();
        $returnTypeName = $returnType ? $returnType->getName() : null;

        $this->method->addMock(
            new Mock($property->getReflection()->getName(), $methodName, $returnTypeName)
        );
    }
}
