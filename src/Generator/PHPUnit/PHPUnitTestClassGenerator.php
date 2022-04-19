<?php
declare(strict_types=1);

namespace Zenas\PHPTestGenerator\Generator\PHPUnit;

use Generator;
use PhpParser\Builder\Class_;
use PhpParser\BuilderFactory;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\DeclareDeclare;
use PhpParser\Node\Stmt\Nop;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zenas\PHPTestGenerator\Configuration\Configuration;
use Zenas\PHPTestGenerator\Model\MethodGenerationContext;
use Zenas\PHPTestGenerator\Model\TestClass;

class PHPUnitTestClassGenerator
{
    /** @var BuilderFactory */
    private $factory;

    /** @var PHPUnitTestMethodGenerator */
    private $methodGenerator;

    /** @var ValueFactoryInterface */
    private $valueFactory;

    /** @var PropertyCommentGenerator */
    private $commentGenerator;

    /** @var MethodArgumentsFactory */
    private $methodArgumentsFactory;

    public function __construct(
        BuilderFactory             $factory,
        PHPUnitTestMethodGenerator $methodGenerator,
        ValueFactoryInterface      $valueFactory,
        PropertyCommentGenerator   $commentGenerator,
        MethodArgumentsFactory     $methodArgumentsFactory
    ) {
        $this->factory = $factory;
        $this->methodGenerator = $methodGenerator;
        $this->valueFactory = $valueFactory;
        $this->commentGenerator = $commentGenerator;
        $this->methodArgumentsFactory = $methodArgumentsFactory;
    }

    public function generate(TestClass $class, Configuration $configuration, bool $dataProviders): array
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
        $this->addMethods($class, $builder, $dataProviders);
        $this->addSetupMethod($class, $builder);

        $uses = [
            $this->factory->use(TestCase::class),
            $this->factory->use(MockObject::class),
            $this->factory->use($class->getClass()->getReflection()->getName()),
        ];

        if ($dataProviders) {
            $uses[] = $this->factory->use(Generator::class);
        }

        $nsBuilder
            ->addStmts($class->getUses())
            ->addStmts($uses)
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
                $statement->setDocComment($this->commentGenerator->generateDependencyComment($type));
            }

            $builder->addStmt($statement);
        }

        $this->addTestedProperty($class, $builder);
    }

    private function addTestedProperty(TestClass $class, Class_ $builder): void
    {
        $builder->addStmt(
            $this->factory->property($class->getTestPropertyName())
                ->setDocComment($this->commentGenerator->generateClassComment($class->getClass()->getReflection()))
                ->makePrivate()
        );
    }

    public function addMethods(TestClass $class, Class_ $builder, bool $dataProviders): void
    {
        foreach ($class->getMethods() as $method) {
            if ($method->isTestable()) {
                $arguments = $this->methodArgumentsFactory->getArgumentsForMethod($method->getReflection());
                $context = new MethodGenerationContext($method, $arguments, $dataProviders);

                $builder->addStmt($this->methodGenerator->generate($context));
                if ($dataProviders) {
                    $builder->addStmt($this->methodGenerator->getDataProvider($context));
                }
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
