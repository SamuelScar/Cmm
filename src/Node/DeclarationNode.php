<?php

namespace Compiler\Node;

use Compiler\Node\ExpressionNode;

class DeclarationNode extends StatementNode
{
    public string $type;
    public string $name;
    public ?ExpressionNode $initializer;

    public function __construct(
        string $type,
        string $name,
        ?ExpressionNode $initializer = null
    ) {
        $this->type        = $type;
        $this->name        = $name;
        $this->initializer = $initializer;
    }
}
