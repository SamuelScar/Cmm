<?php

namespace Compiler\Node;

class WhileNode extends StatementNode
{
    public ExpressionNode $condition;
    public StatementNode  $body;

    public function __construct(ExpressionNode $condition, StatementNode $body)
    {
        $this->condition = $condition;
        $this->body      = $body;
    }
}
