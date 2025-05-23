<?php

namespace Compiler\Node;

/**
 * Representa um parâmetro formal de função na AST.
 *
 * @package Compiler\Node
 */
class ParameterNode
{
    /**
     * Tipo do parâmetro (ex: 'int', 'float', 'char', 'void').
     *
     * @var string
     */
    public string $type;

    /**
     * Nome do parâmetro.
     *
     * @var string
     */
    public string $name;

    /**
     * Construtor do nó de parâmetro.
     *
     * @param string $type Tipo do parâmetro.
     * @param string $name Nome do parâmetro.
     */
    public function __construct(string $type, string $name)
    {
        $this->type = $type;
        $this->name = $name;
    }
}
