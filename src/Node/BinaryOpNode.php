<?php

namespace Compiler\Node;

/**
 * Representa um nó de operador binário na AST.
 *
 * @package Compiler\Node
 */
class BinaryOpNode extends ExpressionNode
{
    /**
     * Operador binário (por exemplo: '+', '-', '*', '/', '&&', '||').
     *
     * @var string
     */
    public string $op;

    /**
     * Expressão à esquerda do operador.
     *
     * @var ExpressionNode
     */
    public ExpressionNode $left;

    /**
     * Expressão à direita do operador.
     *
     * @var ExpressionNode
     */
    public ExpressionNode $right;

    /**
     * Construtor do nó de operação binária.
     *
     * @param string         $op    O símbolo do operador binário.
     * @param ExpressionNode $left  Operando esquerdo.
     * @param ExpressionNode $right Operando direito.
     */
    public function __construct(string $op, ExpressionNode $left, ExpressionNode $right)
    {
        $this->op    = $op;
        $this->left  = $left;
        $this->right = $right;
    }
}
