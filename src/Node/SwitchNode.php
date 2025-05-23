<?php

namespace Compiler\Node;

class SwitchNode extends StatementNode
{
    public ExpressionNode  $expression;
    public array           $cases;
    public array           $defaultStatements;

    public function __construct(
        ExpressionNode $expression,
        array          $cases,
        array          $defaultStatements = []
    ) {
        $this->expression        = $expression;
        $this->cases             = $cases;
        $this->defaultStatements = $defaultStatements;
    }
}
