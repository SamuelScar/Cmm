<?php

namespace Compiler;

/**
 * Representa um token produzido pelo lexer.
 *
 * Contém o tipo, o valor textual, a linha e a coluna.
 *
 * @package Compiler
 */
class Token
{
    /**
     * Construtor do token.
     *
     * @param string $type   Tipo do token (ex: IDENTIFIER, NUMBER, OPERATOR, DELIMITER etc.).
     * @param string $value  Valor textual correspondente ao token.
     * @param int    $line   Linha do token no código-fonte.
     * @param int    $column Coluna do token no código-fonte.
     */
    public function __construct(
        public string $type,
        public string $value,
        public int $line = 0,
        public int $column = 0
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
