<?php

namespace Compiler\Node;

class UnaryOpNode extends ExpressionNode
{
    public string $op;
    public ExpressionNode $expr;

    public function __construct(string $op, ExpressionNode $expr)
    {
        $this->op   = $op;
        $this->expr = $expr;
    }
}
