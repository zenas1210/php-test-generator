<?php

namespace Zenas\PHPTestGenerator\Extractor;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;

class PropertyVariableAssignmentExtractor
{
    public function extract(array $statements): array
    {
        $result = [];

        foreach ($statements as $statement) {
            if (!$statement instanceof Expression) {
                continue;
            }

            $expr = $statement->expr;
            if (!$expr instanceof Assign) {
                continue;
            }

            $var = $expr->var;
            $expr2 = $expr->expr;
            if (!$var instanceof PropertyFetch || !$expr2 instanceof Variable) {
                continue;
            }

            $result[$expr2->name] = $var->name->name;
        }

        return $result;
    }
}
