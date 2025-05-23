<?php

namespace Compiler\Node;

/**
 * Representa o nó raiz do programa na AST.
 * Contém a lista de statements que compõem todo o programa.
 *
 * @package Compiler\Node
 */
class ProgramNode
{
    /**
     * Array de StatementNode que formam o programa.
     *
     * @var StatementNode[]
     */
    public array $statements;

    /**
     * Construtor do nó de programa.
     *
     * @param StatementNode[] $statements Lista de statements do programa.
     */
    public function __construct(array $statements)
    {
        $this->statements = $statements;
    }
}
