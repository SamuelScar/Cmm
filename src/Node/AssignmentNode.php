<?php

namespace Compiler\Node;

class AssignmentNode extends StatementNode
{
    public string $name;
    public ExpressionNode $expression;

    public function __construct(string $name, ExpressionNode $expression)
    {
        $this->name       = $name;
        $this->expression = $expression;
    }
}
