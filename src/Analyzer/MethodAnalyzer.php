<?php

declare(strict_types=1);

namespace Zenas\PHPTestGenerator\Analyzer;

use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeTraverser;
use ReflectionMethod;
use Zenas\PHPTestGenerator\Model\ClassModel;
use Zenas\PHPTestGenerator\Model\Method;
use Zenas\PHPTestGenerator\NodeVisitor\DependencyNodeVisitor;
use Zenas\PHPTestGenerator\NodeVisitor\MethodCallVisitor;
use Zenas\PHPTestGenerator\NodeVisitor\ParentMethodCallVisitor;
use Zenas\PHPTestGenerator\NodeVisitor\ReturnStatementVisitor;
use Zenas\PHPTestGenerator\NodeVisitor\ThrowStatementVisitor;

class MethodAnalyzer
{
    public function analyze(ReflectionMethod $reflection, ClassModel $class, ClassMethod $classMethod): Method
    {
        $method = (new Method())
            ->setReflection($reflection)
            ->setClass($class);

        $this->traverse($method, $classMethod);

        return $method;
    }

    public function traverse(Method $method, ClassMethod $classMethod): void
    {
        $traverser = new NodeTraverser();

        $traverser->addVisitor(new DependencyNodeVisitor($method));
        $traverser->addVisitor(new ThrowStatementVisitor($method));
        $traverser->addVisitor(new ReturnStatementVisitor($method));
        $traverser->addVisitor(new MethodCallVisitor($method));
        $traverser->addVisitor(new ParentMethodCallVisitor($method));

        $traverser->traverse($classMethod->getStmts());
    }
}
