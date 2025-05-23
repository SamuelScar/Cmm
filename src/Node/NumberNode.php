<?php

namespace Compiler\Node;

class NumberNode extends ExpressionNode
{
    public string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }
}
