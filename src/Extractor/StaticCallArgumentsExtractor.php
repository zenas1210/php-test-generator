<?php

namespace Zenas\PHPTestGenerator\Extractor;

use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Expression;

class StaticCallArgumentsExtractor
{
    public function extract(array $statements, string $class, string $method): array
    {
        $result = [];

        foreach ($statements as $statement) {
            if (!$statement instanceof Expression) {
                continue;
            }

            /** @var StaticCall $expr */
            $expr = $statement->expr;
            if (!$expr instanceof StaticCall) {
                continue;
            }

            $classNode = $expr->class;
            if (!$classNode instanceof Name || $classNode->toString() !== $class) {
                continue;
            }

            $name = $expr->name;
            if (!$name instanceof Identifier || $name->toString() !== $method) {
                continue;
            }

            foreach ($expr->args as $i => $arg) {
                $value = $arg->value;
                if ($value instanceof Variable) {
                    $result[$value->name] = $i;
                }
            }

            break;
        }

        return $result;
    }
}
