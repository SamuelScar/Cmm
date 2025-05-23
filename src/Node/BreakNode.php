<?php

namespace Compiler\Node;

/**
 * Representa a instrução de interrupção (break) em loops ou switch na AST.
 *
 * @package Compiler\Node
 */
class BreakNode extends StatementNode
{
    /**
     * Construtor do nó de break.
     *
     * Não recebe parâmetros, pois apenas sinaliza a interrupção do fluxo.
     */
    public function __construct() {}
}
