<?php

namespace Compiler\Node;

/**
 * Representa a instrução de continuação (continue) em loops na AST.
 *
 * @package Compiler\Node
 */
class ContinueNode extends StatementNode
{
    /**
     * Construtor do nó de continue.
     *
     * Não recebe parâmetros, pois apenas sinaliza a continuação do próximo ciclo.
     */
    public function __construct() {}
}
