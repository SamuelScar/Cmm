<?php

namespace Compiler\Node;

class IfNode extends StatementNode
{
    public ExpressionNode $condition;
    public StatementNode  $thenBranch;
    public ?StatementNode $elseBranch;

    public function __construct(
        ExpressionNode $condition,
        StatementNode  $thenBranch,
        ?StatementNode $elseBranch = null
    ) {
        $this->condition  = $condition;
        $this->thenBranch = $thenBranch;
        $this->elseBranch = $elseBranch;
    }
}
