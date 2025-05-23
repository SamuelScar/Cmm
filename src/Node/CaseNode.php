<?php

namespace Compiler\Node;

class CaseNode
{
    public ExpressionNode $value;
    public array          $statements;

    public function __construct(ExpressionNode $value, array $statements)
    {
        $this->value      = $value;
        $this->statements = $statements;
    }
}
