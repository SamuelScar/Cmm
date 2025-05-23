<?php

namespace Compiler\Node;

class ProgramNode
{
    public array $statements;

    public function __construct(array $statements)
    {
        $this->statements = $statements;
    }
}
