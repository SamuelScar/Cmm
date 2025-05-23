<?php

namespace Compiler\Node;

class StringLiteralNode extends ExpressionNode
{
    private string $value;

    public function __construct(string $value)
    {

        $this->value = trim($value, '"');
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
