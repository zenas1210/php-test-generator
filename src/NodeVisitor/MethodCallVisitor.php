<?php

namespace Zenas\PHPTestGenerator\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Zenas\PHPTestGenerator\Model\Method;

class MethodCallVisitor extends NodeVisitorAbstract
{
    private Method $method;

    public function __construct(Method $method)
    {
        $this->method = $method;
    }

    public function leaveNode(Node $node): void
    {
        if (!$node instanceof Node\Expr\MethodCall) {
            return;
        }

        $var = $node->var;
        if (!$var instanceof Node\Expr\Variable || $var->name !== 'this') {
            return;
        }

        $methodName = $node->name->toString();

        $this->method->addMethodCall('this', $methodName);
    }
}
