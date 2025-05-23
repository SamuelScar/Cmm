<?php

namespace Compiler\Node;

/**
 * Representa uma cláusula 'case' dentro de um switch na AST.
 *
 * @package Compiler\Node
 */
class CaseNode
{
    /**
     * Expressão que define o valor do case.
     *
     * @var ExpressionNode
     */
    public ExpressionNode $value;

    /**
     * Lista de statements a serem executados quando o case for correspondido.
     *
     * @var StatementNode[]
     */
    public array $statements;

    /**
     * Construtor do nó de case.
     *
     * @param ExpressionNode   $value      Expressão de comparação do case.
     * @param StatementNode[]  $statements Array de statements dentro do case.
     */
    public function __construct(ExpressionNode $value, array $statements)
    {
        $this->value      = $value;
        $this->statements = $statements;
    }
}
