<?php

namespace Compiler\Node;

class ExpressionStatementNode extends StatementNode
{
    private ExpressionNode $expr;

    public function __construct(ExpressionNode $expr)
    {
        $this->expr = $expr;
    }

    public function getExpression(): ExpressionNode
    {
        return $this->expr;
    }
}
