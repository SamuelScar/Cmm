<?php

namespace Compiler\Node;

/**
 * Representa uma instrução switch na AST.
 *
 * @package Compiler\Node
 */
class SwitchNode extends StatementNode
{
    /**
     * Expressão a ser avaliada no switch.
     *
     * @var ExpressionNode
     */
    public ExpressionNode $expression;

    /**
     * Lista de nós CaseNode correspondentes às cláusulas case.
     *
     * @var CaseNode[]
     */
    public array $cases;

    /**
     * Lista de statements do bloco default (opcional).
     *
     * @var StatementNode[]
     */
    public array $defaultStatements;

    /**
     * Construtor do nó de switch.
     *
     * @param ExpressionNode $expression        Expressão do switch.
     * @param CaseNode[]     $cases             Array de nós de case.
     * @param StatementNode[] $defaultStatements Array de statements do default.
     */
    public function __construct(
        ExpressionNode $expression,
        array $cases,
        array $defaultStatements = []
    ) {
        $this->expression        = $expression;
        $this->cases             = $cases;
        $this->defaultStatements = $defaultStatements;
    }
}
