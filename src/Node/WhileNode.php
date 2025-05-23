<?php

namespace Compiler\Node;

/**
 * Representa um laço de repetição while na AST.
 *
 * @package Compiler\Node
 */
class WhileNode extends StatementNode
{
    /**
     * Expressão de condição para manter o laço.
     *
     * @var ExpressionNode
     */
    public ExpressionNode $condition;

    /**
     * Corpo do laço a ser executado enquanto a condição for verdadeira.
     *
     * @var StatementNode
     */
    public StatementNode $body;

    /**
     * Construtor do nó while.
     *
     * @param ExpressionNode $condition Expressão de controle do laço.
     * @param StatementNode  $body      Statement ou bloco a ser executado.
     */
    public function __construct(ExpressionNode $condition, StatementNode $body)
    {
        $this->condition = $condition;
        $this->body      = $body;
    }
}
