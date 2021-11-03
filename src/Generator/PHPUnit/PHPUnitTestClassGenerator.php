<?php

namespace Zenas\PHPTestGenerator\Generator\PHPUnit;

use PhpParser\Builder\Class_;
use PhpParser\BuilderFactory;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\DeclareDeclare;
use PhpParser\Node\Stmt\Nop;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zenas\PHPTestGenerator\Configuration\Configuration;
use Zenas\PHPTestGenerator\Model\TestClass;

class PHPUnitTestClassGenerator
{
    private BuilderFactory $factory;
    private PHPUnitTestMethodGenerator $methodGenerator;
    private ValueFactoryInterface $valueFactory;
    private PropertyCommentGenerator $commentGenerator;

    public function __construct(
        BuilderFactory             $factory,
        PHPUnitTestMethodGenerator $methodGenerator,
        ValueFactoryInterface      $valueFactory,
        PropertyCommentGenerator   $commentGenerator
    ) {
        $this->factory = $factory;
        $this->methodGenerator = $methodGenerator;
        $this->valueFactory = $valueFactory;
        $this->commentGenerator = $commentGenerator;
    }

    public function generate(TestClass $class, Configuration $configuration): array
    {
        $testNamespace = str_replace(
            $configuration->getSourceNamespace(),
            $configuration->getTestsNamespace(),
            $class->getClass()->getReflection()->getNamespaceName()
        );

        $nsBuilder = $this->factory->namespace($testNamespace);

        $class
            ->setNamespace($testNamespace)
            ->setShortName($class->getClass()->getReflection()->getShortName() . 'Test');

        $builder = $this->factory->class($class->getShortName())
            ->extend('TestCase');

        $this->addProperties($class, $builder);
        $this->addMethods($class, $builder);
        $this->addSetupMethod($class, $builder);

        $nsBuilder
            ->addStmts($class->getUses())
            ->addStmts(
                [
                    $this->factory->use(TestCase::class),
                    $this->factory->use(MockObject::class),
                    $this->factory->use($class->getClass()->getReflection()->getName()),
                ]
            )
            ->addStmt(new Nop())
            ->addStmt($builder);

        return [
            new Declare_([new DeclareDeclare('strict_types', $this->factory->val(1))]),
            new Nop(),
            $nsBuilder->getNode(),
        ];
    }

    private function addProperties(TestClass $class, Class_ $builder): void
    {
        foreach ($class->getClass()->getProperties() as $name => $parameter) {
            $statement = $this->factory->property($name)
                ->makePrivate();

            $type = $parameter->getType();
            if ($type !== null) {
                $statement->setDocComment($this->commentGenerator->generateComment($type));
            }

            $builder->addStmt($statement);
        }

        $this->addTestedProperty($class, $builder);
    }

    private function addTestedProperty(TestClass $class, Class_ $builder): void
    {
        $builder->addStmt(
            $this->factory->property($class->getTestPropertyName())
                ->setType($class->getClass()->getReflection()->getShortName())
                ->makePrivate()
        );
    }

    public function addMethods(TestClass $class, Class_ $builder): void
    {
        foreach ($class->getMethods() as $method) {
            if ($method->isTestable()) {
                $builder->addStmt($this->methodGenerator->generate($method, $class));
            }
        }
    }

    private function addSetupMethod(TestClass $class, Class_ $builder): void
    {
        $arguments = $this->getConstructorArguments($class);

        $methodBuilder = $this->factory->method('setUp')
            ->makeProtected()
            ->setReturnType('void');

        foreach ($arguments as $name => $argument) {
            $methodBuilder->addStmt(
                new Assign(
                    $this->factory->var('this->' . $name),
                    $argument
                )
            );
        }

        $arguments = array_map(
            function (string $argument) {
                return $this->factory->var('this->' . $argument);
            },
            array_keys($arguments)
        );

        $methodBuilder->addStmt(
            new Assign(
                $this->factory->var('this->' . $class->getTestPropertyName()),
                $this->factory->new(
                    $class->getClass()->getReflection()->getShortName(),
                    $arguments
                )
            )
        );

        $builder->addStmt($methodBuilder->getNode());
    }

    private function getConstructorArguments(TestClass $class): array
    {
        $arguments = [];
        foreach ($class->getClass()->getConstructorParameters() as $name => $parameter) {
            $type = $parameter->getType();
            if ($type === null) {
                $typeName = null;
            } else {
                $typeName = $type->getClassName() ?: $type->getBuiltinType();
            }

            $value = $this->valueFactory->getValueForType($typeName);
            $arguments[$name] = $this->factory->val($value);
        }

        return $arguments;
    }
}
