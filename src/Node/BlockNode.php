<?php

namespace Compiler\Node;

/**
 * Representa um bloco de código que contém múltiplos statements na AST.
 *
 * @package Compiler\Node
 */
class BlockNode extends StatementNode
{
    /**
     * Lista de nós de instruções (statements) dentro do bloco.
     *
     * @var StatementNode[]
     */
    public array $statements;

    /**
     * Construtor do nó de bloco.
     *
     * @param StatementNode[] $statements Array de statements que compõem o bloco.
     */
    public function __construct(array $statements)
    {
        $this->statements = $statements;
    }
}
