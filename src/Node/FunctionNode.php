<?php

namespace Compiler\Node;

/**
 * Representa a definição de uma função na AST.
 *
 * @package Compiler\Node
 */
class FunctionNode extends StatementNode
{
    /**
     * Tipo de retorno da função (por exemplo: 'int', 'void').
     *
     * @var string
     */
    public string $returnType;

    /**
     * Nome identificador da função.
     *
     * @var string
     */
    public string $name;

    /**
     * Lista de parâmetros formais da função.
     *
     * @var ParameterNode[]
     */
    public array $parameters;

    /**
     * Corpo da função, encapsulado em um bloco de statements.
     *
     * @var BlockNode
     */
    public BlockNode $body;

    /**
     * Construtor do nó de definição de função.
     *
     * @param string           $returnType Tipo de retorno da função.
     * @param string           $name       Nome da função.
     * @param ParameterNode[]  $parameters Lista de parâmetros formais.
     * @param BlockNode        $body       Corpo da função em forma de bloco.
     */
    public function __construct(
        string $returnType,
        string $name,
        array $parameters,
        BlockNode $body
    ) {
        $this->returnType = $returnType;
        $this->name       = $name;
        $this->parameters = $parameters;
        $this->body       = $body;
    }
}
