<?php

namespace Compiler\Node;

/**
 * Representa um literal numérico na AST.
 *
 * @package Compiler\Node
 */
class NumberNode extends ExpressionNode
{
    /**
     * Valor textual do literal numérico.
     *
     * @var string
     */
    public string $value;

    /**
     * Construtor do nó de literal numérico.
     *
     * @param string $value Valor do número como string.
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }
}
