<?php

namespace Compiler\Node;

/**
 * Representa um literal de string na AST.
 *
 * @package Compiler\Node
 */
class StringLiteralNode extends ExpressionNode
{
    /**
     * Valor da string, sem as aspas delimitadoras.
     *
     * @var string
     */
    private string $value;

    /**
     * Construtor do nó de literal de string.
     *
     * Remove as aspas delimitadoras e armazena o valor.
     *
     * @param string $value Texto literal incluindo aspas.
     */
    public function __construct(string $value)
    {
        $this->value = trim($value, '"');
    }

    /**
     * Retorna o valor da string sem aspas.
     *
     * @return string Texto da string.
     */
    public function getValue(): string
    {
        return $this->value;
    }
}
