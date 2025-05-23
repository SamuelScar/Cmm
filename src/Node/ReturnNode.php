<?php

namespace Compiler\Node;

class ReturnNode extends StatementNode
{
    public ?ExpressionNode $expression;

    public function __construct(?ExpressionNode $expression = null)
    {
        $this->expression = $expression;
    }
}
