<?php

namespace Zenas\PHPTestGenerator\Generator\PHPUnit;

use PhpParser\Builder\Method as ParserMethod;
use PhpParser\BuilderFactory;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use Zenas\PHPTestGenerator\Model\Method;
use Zenas\PHPTestGenerator\Model\Mock;
use Zenas\PHPTestGenerator\Model\TestClass;

class PHPUnitTestMethodGenerator
{
    /** @var BuilderFactory */
    private $factory;

    /** @var ValueFactoryInterface */
    private $valueFactory;

    /** @var MethodArgumentsFactory */
    private $methodArgumentsFactory;

    public function __construct(
        BuilderFactory         $factory,
        ValueFactoryInterface  $valueFactory,
        MethodArgumentsFactory $methodArgumentsFactory
    ) {
        $this->factory = $factory;
        $this->valueFactory = $valueFactory;
        $this->methodArgumentsFactory = $methodArgumentsFactory;
    }

    public function generate(Method $method, TestClass $class): ParserMethod
    {
        $builder = $this->getBaseBuilder($method);

        $arguments = $this->addArguments($method, $builder);
        $this->addMocks($method, $builder);
        $this->addExceptions($method, $builder);
        $this->addAssertResult($method, $builder, $arguments, $class);

        return $builder;
    }

    public function getBaseBuilder(Method $method): ParserMethod
    {
        return $this->factory->method('test' . ucfirst($method->getReflection()->getName()))
            ->makePublic()
            ->setReturnType('void');
    }

    public function addArguments(Method $method, ParserMethod $builder): array
    {
        $arguments = $this->methodArgumentsFactory->getArgumentsForMethod($method->getReflection());

        foreach ($arguments as $name => $value) {
            $builder->addStmt(
                new Assign(
                    new Variable($name),
                    $this->factory->val($value)
                )
            );
        }

        return $arguments;
    }

    public function addMocks(Method $method, ParserMethod $builder): void
    {
        foreach ($method->getMocks() as $mock) {
            $this->addMock($mock, $builder);
        }
    }

    public function addMock(Mock $mock, ParserMethod $builder): void
    {
        $type = $mock->getVariableType();
        $value = $this->valueFactory->getValueForType($type);

        $expects = $this->factory->methodCall(
            $this->factory->var('this->' . $mock->getDependecy()),
            'expects',
            [
                $this->factory->methodCall(
                    $this->factory->var('this'),
                    'atLeastOnce'
                ),
            ]
        );

        $method = $this->factory->methodCall(
            $expects,
            'method',
            [$mock->getMethod()]
        );

        if (in_array($type, [null, 'void'])) {
            $builder->addStmt($method);

            return;
        }

        $variableName = sprintf('mocked%s', ucfirst($mock->getMethod()));
        $variable = new Variable($variableName);

        $builder
            ->addStmt(
                new Assign(
                    $variable,
                    $this->factory->val($value)
                )
            )->addStmt(
                $this->factory->methodCall($method, 'willReturn', [$variable])
            );
    }

    public function addExceptions(Method $method, ParserMethod $builder): void
    {
        foreach ($method->getExceptions() as $exception) {
            $this->addException($exception, $builder);
        }
    }

    public function addException(string $class, ParserMethod $builder)
    {
        $builder->addStmt(
            new Expression(
                $this->factory->methodCall(
                    $this->factory->var('this'),
                    'expectException',
                    [$this->factory->classConstFetch($class, 'class'),]
                )
            )
        );
    }

    public function addAssertResult(Method $method, ParserMethod $builder, array $arguments, TestClass $class): void
    {
        $reflection = $method->getReflection();

        $methodName = 'methodCall';
        $var = $this->factory->var('this->' . $class->getTestPropertyName());

        if ($reflection->isStatic()) {
            $methodName = 'staticCall';
            $var = $method->getClass()->getReflection()->getName();
        }

        $methodCall = $this->factory->$methodName(
            $var,
            $reflection->getName(),
            $this->wrapArray($arguments)
        );

        if (!$method->hasReturn() || in_array($method->getReturnType(), [null, 'void'])) {
            $builder->addStmt($methodCall);

            return;
        }

        $expected = $this->factory->var('expected');
        $result = $this->factory->var('result');
        $expectedValue = $this->valueFactory->getValueForType($method->getReturnType());

        $builder
            ->addStmt(new Assign($expected, $this->factory->val($expectedValue)))
            ->addStmt(new Assign($result, $methodCall))
            ->addStmt(
                $this->factory->methodCall(
                    $this->factory->var('this'),
                    'assertEquals',
                    [$expected, $result]
                )
            );
    }

    public function wrapArray(array $array): array
    {
        $result = [];

        foreach ($array as $name => $item) {
            $result[] = $this->factory->var($name);
        }

        return $result;
    }
}
