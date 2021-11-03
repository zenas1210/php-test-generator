<?php

declare(strict_types=1);

namespace Zenas\PHPTestGenerator\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeVisitorAbstract;
use Zenas\PHPTestGenerator\Model\Method;

class ReturnStatementVisitor extends NodeVisitorAbstract
{
    private Method $method;

    public function __construct(Method $method)
    {
        $this->method = $method;
    }

    public function leaveNode(Node $node): void
    {
        if (!$node instanceof Return_ || $node->expr === null) {
            return;
        }

        $this->method->setHasReturn(true);
    }
}
