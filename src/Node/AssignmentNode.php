<?php

namespace Compiler\Node;

/**
 * Representa uma instrução de atribuição na AST.
 *
 * @package Compiler\Node
 */
class AssignmentNode extends StatementNode
{
    /**
     * Nome da variável que recebe o valor.
     *
     * @var string
     */
    public string $name;

    /**
     * Expressão cujo resultado será atribuído à variável.
     *
     * @var ExpressionNode
     */
    public ExpressionNode $expression;

    /**
     * Construtor do nó de atribuição.
     *
     * @param string         $name       Identificador da variável.
     * @param ExpressionNode $expression Expressão a ser avaliada e atribuída.
     */
    public function __construct(string $name, ExpressionNode $expression)
    {
        $this->name       = $name;
        $this->expression = $expression;
    }
}
