<?php

namespace Compiler\Node;

/**
 * Representa um laço for na AST, com inicialização, condição, passo e corpo.
 *
 * @package Compiler\Node
 */
class ForNode extends StatementNode
{
    /**
     * Instrução de inicialização do laço (declaração ou atribuição), ou null.
     *
     * @var StatementNode|null
     */
    public ?StatementNode $init;

    /**
     * Expressão de condição do laço, avaliável como booleano, ou null.
     *
     * @var ExpressionNode|null
     */
    public ?ExpressionNode $condition;

    /**
     * Instrução de passo executada a cada iteração (geralmente incremento), ou null.
     *
     * @var StatementNode|null
     */
    public ?StatementNode $post;

    /**
     * Corpo do laço, que pode ser um bloco ou um único statement.
     *
     * @var StatementNode
     */
    public StatementNode $body;

    /**
     * Construtor do nó de laço for.
     *
     * @param StatementNode|null  $init      Inicialização do laço.
     * @param ExpressionNode|null $condition Condição de continuação do laço.
     * @param StatementNode|null  $post      Passo executado após cada iteração.
     * @param StatementNode       $body      Corpo a ser executado em cada iteração.
     */
    public function __construct(
        ?StatementNode $init,
        ?ExpressionNode $condition,
        ?StatementNode $post,
        StatementNode $body
    ) {
        $this->init      = $init;
        $this->condition = $condition;
        $this->post      = $post;
        $this->body      = $body;
    }
}
