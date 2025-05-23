<?php

namespace Compiler\Node;

/**
 * Representa uma instrução condicional if na AST.
 *
 * @package Compiler\Node
 */
class IfNode extends StatementNode
{
    /**
     * Expressão que determina o caminho do if.
     *
     * @var ExpressionNode
     */
    public ExpressionNode $condition;

    /**
     * Statement executado quando a condição for verdadeira.
     *
     * @var StatementNode
     */
    public StatementNode $thenBranch;

    /**
     * Statement executado quando a condição for falsa (opcional).
     *
     * @var StatementNode|null
     */
    public ?StatementNode $elseBranch;

    /**
     * Construtor do nó de instrução if.
     *
     * @param ExpressionNode      $condition  Expressão condicional.
     * @param StatementNode       $thenBranch Bloco ou statement a ser executado se verdadeiro.
     * @param StatementNode|null  $elseBranch Bloco ou statement a ser executado se falso (opcional).
     */
    public function __construct(
        ExpressionNode $condition,
        StatementNode $thenBranch,
        ?StatementNode $elseBranch = null
    ) {
        $this->condition  = $condition;
        $this->thenBranch = $thenBranch;
        $this->elseBranch = $elseBranch;
    }
}
