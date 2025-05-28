<?php

namespace Compiler;

use Exception;

/**
 * Exceção personalizada para erros sintáticos.
 *
 * Inclui linha, coluna, tipo e valor do token.
 */
class SyntaxError extends Exception
{
    public function __construct(string $message, Token $token)
    {
        parent::__construct(sprintf(
            "Linha %d, Coluna %d: %s (Token: %s '%s')",
            $token->line,
            $token->column,
            $message,
            $token->type,
            $token->value
        ));
    }
}
