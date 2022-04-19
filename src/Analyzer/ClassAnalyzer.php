<?php

declare(strict_types=1);

namespace Zenas\PHPTestGenerator\Analyzer;

use PhpParser\PrettyPrinter\Standard;
use ReflectionClass;
use ReflectionMethod;
use Zenas\PHPTestGenerator\Configuration\Configuration;
use Zenas\PHPTestGenerator\Container\AstContainer;
use Zenas\PHPTestGenerator\Factory\AstContainerFactory;
use Zenas\PHPTestGenerator\Factory\ClassModelFactory;
use Zenas\PHPTestGenerator\Generator\PHPUnit\PHPUnitTestClassGenerator;
use Zenas\PHPTestGenerator\Model\GeneratedTestClass;
use Zenas\PHPTestGenerator\Model\Method;
use Zenas\PHPTestGenerator\Model\TestClass;
use Zenas\PHPTestGenerator\Resolver\ShortClassNameResolver;

class ClassAnalyzer
{
    /** @var ReflectionClass */
    public $reflection;

    /** @var PHPUnitTestClassGenerator  */
    private $classGenerator;

    /** @var ShortClassNameResolver */
    private $shortClassNameProvider;

    /** @var ClassModelFactory */
    private $classModelFactory;

    /** @var AstContainerFactory */
    private $astContainerFactory;

    /** @var MethodAnalyzer */
    private $methodAnalyzer;

    public function __construct(
        ShortClassNameResolver    $shortClassNameProvider,
        ClassModelFactory         $classModelFactory,
        PHPUnitTestClassGenerator $classGenerator,
        AstContainerFactory $astContainerFactory,
        MethodAnalyzer $methodAnalyzer
    ) {
        $this->shortClassNameProvider = $shortClassNameProvider;
        $this->classModelFactory = $classModelFactory;
        $this->classGenerator = $classGenerator;
        $this->astContainerFactory = $astContainerFactory;
        $this->methodAnalyzer = $methodAnalyzer;
    }

    public function generate(Configuration $configuration, string $className, bool $dataProviders): GeneratedTestClass
    {
        $this->reflection = new ReflectionClass($className);
        $astContainer = $this->astContainerFactory->create($this->reflection);
        $classMetadata = $this->classModelFactory->create($this->reflection, $astContainer);
        $class = new TestClass($classMetadata);
        $class->setUses($astContainer->getUses());
        $this->shortClassNameProvider->setClass($class);

        $code = $this->generateTestClass($class, $configuration, $astContainer, $dataProviders);

        return new GeneratedTestClass($class->getFQCN(), $code);
    }

    private function generateTestClass(TestClass $class, Configuration $configuration, AstContainer $container, bool $dataProviders): string
    {
        $this->addMethods($class, $container);
        $nodes = $this->classGenerator->generate($class, $configuration, $dataProviders);

        return (new Standard())->prettyPrintFile($nodes);
    }

    private function addMethods(TestClass $class, AstContainer $astContainer): void
    {
        foreach ($this->reflection->getMethods() as $method) {
            $this->addMethod($method, $class, $astContainer);
        }

        foreach ($class->getMethods() as $method) {
            $this->addChildMocks($method);
        }
    }

    private function addChildMocks(Method $method, array $called = []): void
    {
        foreach ($method->getMethodCalls() as $methodCall) {
            $call = explode('::', $methodCall);
            if ($call[0] === 'this') {
                if (in_array($call[1], $called)) {
                    continue;
                }
                $called[] = $call[1];
            }

            $calledMethod = $method->getTestClass()->getMethod($call[1]);
            foreach ($calledMethod->getMocks() as $mock) {
                $method->addMock($mock);
                $this->addChildMocks($calledMethod, $called);
            }
        }
    }

    private function addMethod(ReflectionMethod $reflection, TestClass $class, AstContainer $astContainer): void
    {
        if ($reflection->name !== '__invoke' && strpos($reflection->name, '__') === 0) {
            return;
        }

        $methodAst = $astContainer->getMethod($reflection->name);
        $analyzedMethod = $this->methodAnalyzer->analyze($reflection, $class->getClass(), $methodAst);
        $analyzedMethod->setTestClass($class);
        $class->addMethod($analyzedMethod);
    }
}
