<?php

namespace Compiler\Node;

use Compiler\Node\ExpressionNode;

/**
 * Representa uma declaração de variável na AST.
 *
 * @package Compiler\Node
 */
class DeclarationNode extends StatementNode
{
    /**
     * Tipo da variável declarada (ex: 'int', 'float', 'char', 'void').
     *
     * @var string
     */
    public string $type;

    /**
     * Nome identificador da variável.
     *
     * @var string
     */
    public string $name;

    /**
     * Expressão inicializadora opcional atribuída à variável.
     *
     * @var ExpressionNode|null
     */
    public ?ExpressionNode $initializer;

    /**
     * Construtor do nó de declaração.
     *
     * @param string              $type        Tipo da variável (lowercase).
     * @param string              $name        Nome da variável.
     * @param ExpressionNode|null $initializer Expressão inicializadora (ou null se ausente).
     */
    public function __construct(
        string $type,
        string $name,
        ?ExpressionNode $initializer = null
    ) {
        $this->type        = $type;
        $this->name        = $name;
        $this->initializer = $initializer;
    }
}
