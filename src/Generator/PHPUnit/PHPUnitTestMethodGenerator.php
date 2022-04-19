<?php
declare(strict_types=1);

namespace Zenas\PHPTestGenerator\Generator\PHPUnit;

use Generator;
use PhpParser\Builder\Method as ParserMethod;
use PhpParser\BuilderFactory;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Stmt\Expression;
use Zenas\PHPTestGenerator\Model\Method;
use Zenas\PHPTestGenerator\Model\MethodGenerationContext;
use Zenas\PHPTestGenerator\Model\Mock;

class PHPUnitTestMethodGenerator
{
    /** @var BuilderFactory */
    private $factory;

    /** @var ValueFactoryInterface */
    private $valueFactory;

    public function __construct(
        BuilderFactory         $factory,
        ValueFactoryInterface  $valueFactory
    ) {
        $this->factory = $factory;
        $this->valueFactory = $valueFactory;
    }

    public function generate(MethodGenerationContext $context): ParserMethod
    {
        $builder = $this->getBaseBuilder($context);

        $method = $context->getMethod();
        $arguments = $context->getArguments();

        if (!$context->hasDataProviders()) {
            $this->addArguments($builder, $arguments);
        } elseif ($method->hasNonVoidReturn()) {
            $builder->addParam($this->factory->param('expected'));
        }

        $this->addMocks($method, $builder);
        $this->addExceptions($method, $builder);
        $this->addAssertResult($context, $builder);

        if ($context->hasDataProviders()) {
            $builder->setDocComment(sprintf("/**\n* @dataProvider %s\n*/", $this->getDataProviderName($context)));
        }

        return $builder;
    }

    public function getDataProvider(MethodGenerationContext $context): ParserMethod
    {
        $method = $context->getMethod();
        $arguments = $context->getArguments();

        $builder = $this->factory->method($this->getDataProviderName($context))
            ->makePublic()
            ->setReturnType(Generator::class);

        if ($method->hasNonVoidReturn()) {
            $arguments['expected'] = $this->valueFactory->getValueForType($method->getReturnType());
        }

        $yield = new Yield_($this->factory->val($arguments));

        $builder->addStmt($yield);

        return $builder;
    }

    public function getDataProviderName(MethodGenerationContext $context): string
    {
        return $context->getMethod()->getReflection()->getName() . 'Provider';
    }

    public function getBaseBuilder(MethodGenerationContext $context): ParserMethod
    {
        $builder = $this->factory->method('test' . ucfirst($context->getMethod()->getReflection()->getName()))
            ->makePublic()
            ->setReturnType('void');

        if (!$context->hasDataProviders()) {
            return $builder;
        }

        foreach ($context->getArguments() as $name => $value) {
            $builder->addParam($this->factory->param($name));
        }

        return $builder;
    }

    public function addArguments(ParserMethod $builder, array $arguments): void
    {
        foreach ($arguments as $name => $value) {
            $builder->addStmt(
                new Assign(
                    new Variable($name),
                    $this->factory->val($value)
                )
            );
        }
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

    public function addException(string $class, ParserMethod $builder): void
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

    public function addAssertResult(MethodGenerationContext $context, ParserMethod $builder): void
    {
        $method = $context->getMethod();
        $reflection = $method->getReflection();

        $methodName = 'methodCall';
        $var = $this->factory->var('this->' . $method->getTestClass()->getTestPropertyName());

        if ($reflection->isStatic()) {
            $methodName = 'staticCall';
            $var = $method->getClass()->getReflection()->getName();
        }

        $methodCall = $this->factory->$methodName(
            $var,
            $reflection->getName(),
            $this->wrapArray($context->getArguments())
        );

        if (!$method->hasNonVoidReturn()) {
            $builder->addStmt($methodCall);

            return;
        }

        $expected = $this->factory->var('expected');
        $result = $this->factory->var('result');

        if (!$context->hasDataProviders()) {
            $expectedValue = $this->valueFactory->getValueForType($method->getReturnType());

            $builder->addStmt(new Assign($expected, $this->factory->val($expectedValue)));
        }

        $builder
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
