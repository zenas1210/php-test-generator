<?php

declare(strict_types=1);

namespace Zenas\PHPTestGenerator\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\Throw_;
use PhpParser\NodeVisitorAbstract;
use Zenas\PHPTestGenerator\Model\Method;

class ThrowStatementVisitor extends NodeVisitorAbstract
{
    /** @var Method */
    private $method;

    public function __construct(Method $method)
    {
        $this->method = $method;
    }

    public function leaveNode(Node $node): void
    {
        $expr = $node->expr ?? null;
        if (!$node instanceof Throw_ || !$expr instanceof Node\Expr\New_) {
            return;
        }

        $class = $expr->class;
        if ($class instanceof Node\Name) {
            $name = $class->toString();
        } else {
            $name = $class->name->toString();
        }

        $this->method->addException($name);
    }
}
