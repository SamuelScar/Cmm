<?php

namespace Compiler\Node;

class BlockNode extends StatementNode
{
    public array $statements;

    public function __construct(array $statements)
    {
        $this->statements = $statements;
    }
}
