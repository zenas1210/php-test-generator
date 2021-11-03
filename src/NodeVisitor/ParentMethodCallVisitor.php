<?php

namespace Zenas\PHPTestGenerator\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Zenas\PHPTestGenerator\Model\Method;

class ParentMethodCallVisitor extends NodeVisitorAbstract
{
    private Method $method;

    public function __construct(Method $method)
    {
        $this->method = $method;
    }

    public function leaveNode(Node $node): void
    {
        if (!$node instanceof Node\Expr\StaticCall) {
            return;
        }

        $class = $node->class;
        if (!$class instanceof Node\Name || $class->toString() !== 'parent') {
            return;
        }

        $methodName = $node->name->toString();

        $this->method->addMethodCall('parent', $methodName);
    }
}
