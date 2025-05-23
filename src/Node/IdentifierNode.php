<?php

namespace Compiler\Node;

/**
 * Representa um identificador (nome de variável, função, etc.) na AST.
 *
 * @package Compiler\Node
 */
class IdentifierNode extends ExpressionNode
{
    /**
     * Nome do identificador.
     *
     * @var string
     */
    public string $name;

    /**
     * Construtor do nó de identificador.
     *
     * @param string $name Nome do identificador.
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
