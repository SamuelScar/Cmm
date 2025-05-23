<?php

namespace Compiler\Node;

/**
 * Representa um statement que envolve uma expressão na AST.
 *
 * @package Compiler\Node
 */
class ExpressionStatementNode extends StatementNode
{
    /**
     * Expressão encapsulada como statement.
     *
     * @var ExpressionNode
     */
    private ExpressionNode $expr;

    /**
     * Construtor do nó de statement de expressão.
     *
     * @param ExpressionNode $expr Expressão a ser avaliada como statement.
     */
    public function __construct(ExpressionNode $expr)
    {
        $this->expr = $expr;
    }

    /**
     * Retorna a expressão contida neste statement.
     *
     * @return ExpressionNode Expressão encapsulada.
     */
    public function getExpression(): ExpressionNode
    {
        return $this->expr;
    }
}
