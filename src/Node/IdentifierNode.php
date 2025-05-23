<?php

namespace Compiler\Node;

class IdentifierNode extends ExpressionNode
{
    public string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
