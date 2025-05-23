<?php

namespace Compiler;

/**
 * Representa um token produzido pelo lexer.
 *
 * Contém o tipo e o valor textual do token.
 *
 * @package Compiler
 */
class Token
{
    /**
     * Construtor do token.
     *
     * @param string $type  Tipo do token (ex: IDENTIFIER, NUMBER, OPERATOR, DELIMITER etc.).
     * @param string $value Valor textual correspondente ao token.
     */
    public function __construct(
        public string $type,
        public string $value
    ) {}

    /**
     * Retorna a representação textual do token no formato "[TIPO: valor]".
     *
     * @return string
     */
    public function __toString(): string
    {
        return "[{$this->type}: {$this->value}]";
    }
}
