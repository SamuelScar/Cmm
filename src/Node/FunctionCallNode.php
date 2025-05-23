<?php

namespace Compiler\Node;

class FunctionCallNode extends ExpressionNode
{
    private string $name;
    private array $arguments;

    public function __construct(string $name, array $arguments = [])
    {
        $this->name      = $name;
        $this->arguments = $arguments;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }
}
