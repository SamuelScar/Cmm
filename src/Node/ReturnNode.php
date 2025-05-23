<?php

namespace Compiler\Node;

/**
 * Representa uma instrução de retorno na AST.
 *
 * @package Compiler\Node
 */
class ReturnNode extends StatementNode
{
    /**
     * Expressão opcional cujo valor será retornado.
     *
     * @var ExpressionNode|null
     */
    public ?ExpressionNode $expression;

    /**
     * Construtor do nó de return.
     *
     * @param ExpressionNode|null $expression Expressão a ser retornada (ou null para retorno vazio).
     */
    public function __construct(?ExpressionNode $expression = null)
    {
        $this->expression = $expression;
    }
}
