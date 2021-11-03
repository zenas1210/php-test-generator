<?php

namespace Zenas\PHPTestGenerator\Factory;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use ReflectionClass;
use Zenas\PHPTestGenerator\Container\AstContainer;

class AstContainerFactory
{
    private NodeFinder $nodeFinder;
    private ParserFactory $parserFactory;

    public function __construct(NodeFinder $nodeFinder, ParserFactory $parserFactory)
    {
        $this->nodeFinder = $nodeFinder;
        $this->parserFactory = $parserFactory;
    }

    public function create(ReflectionClass $reflectionClass): AstContainer
    {
        $parser = $this->parserFactory->create(ParserFactory::PREFER_PHP7);

        $path = $reflectionClass->getFileName();
        $code = file_get_contents($path);
        $ast = $parser->parse($code);

        /** @var Namespace_ $namespace */
        $namespace = $this->nodeFinder->findFirstInstanceOf($ast, Namespace_::class);

        /** @var Class_ $class */
        $class = $this->nodeFinder->findFirstInstanceOf($namespace, Class_::class);

        $parent = $reflectionClass->getParentClass() ? $this->create($reflectionClass->getParentClass()) : null;

        return (new AstContainer())
            ->setUses($this->getUseStatements($namespace))
            ->setMethods($this->getMethods($class))
            ->setParentContainer($parent);
    }

    protected function getUseStatements(Namespace_ $namespace): array
    {
        $result = [];

        /** @var UseUse[] $uses */
        $uses = $this->nodeFinder->findInstanceOf($namespace, UseUse::class);
        foreach ($uses as $use) {
            if ($use->name->toString() !== $use->name->toLowerString()) {
                $result[$use->name->toString()] = new Use_([$use], $use->type, $use->getAttributes());
            }
        }

        return $result;
    }

    protected function getMethods(Class_ $class): array
    {
        /** @var ClassMethod[] $nodes */
        $nodes = $this->nodeFinder->findInstanceOf($class, ClassMethod::class);
        $methods = [];

        foreach ($nodes as $stmt) {
            $methods[$stmt->name->name] = $stmt;
        }

        return $methods;
    }
}
