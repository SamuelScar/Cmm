<?php

namespace Compiler\Node;

/**
 * Representa um nó de operação unária na AST.
 *
 * @package Compiler\Node
 */
class UnaryOpNode extends ExpressionNode
{
    /**
     * Operador unário (por exemplo: '!', '+' ou '-').
     *
     * @var string
     */
    public string $op;

    /**
     * Expressão à qual o operador unário é aplicado.
     *
     * @var ExpressionNode
     */
    public ExpressionNode $expr;

    /**
     * Construtor do nó de operação unária.
     *
     * @param string         $op   Símbolo do operador unário.
     * @param ExpressionNode $expr Expressão alvo do operador.
     */
    public function __construct(string $op, ExpressionNode $expr)
    {
        $this->op   = $op;
        $this->expr = $expr;
    }
}
