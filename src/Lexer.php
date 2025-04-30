<?php

namespace Compiler;
use Exception;

class Lexer
{
    private string $source;
    private int $pos = 0;
    private int $length;
    private array $tokens = [];

    private array $tokenPatterns = [
        'WHITESPACE' => '/^\s+/',
        'COMMENT'    => '/^\/\/.*|^\/\*[\s\S]*?\*\//',
        'KEYWORD'    => '/^\b(int|float|if|else|return|while|for|void)\b/',
        'IDENTIFIER' => '/^[a-zA-Z_][a-zA-Z0-9_]*/',
        'NUMBER'     => '/^\d+(\.\d+)?/',
        'OPERATOR'   => '/^(==|!=|<=|>=|\|\||&&|[+\-*\/=<>])/',
        'DELIMITER'  => '/^[()\[\]{};,]/',
    ];

    public function __construct(string $source)
    {
        $this->source = $source;
        $this->length = strlen($source);
    }

    public function tokenize(): array
    {
        while ($this->pos < $this->length) {
            $remaining = substr($this->source, $this->pos);
            $matched = false;

            foreach ($this->tokenPatterns as $type => $pattern) {
                if (preg_match($pattern, $remaining, $matches)) {
                    $value = $matches[0];
                    $matched = true;

                    if ($type !== 'WHITESPACE' && $type !== 'COMMENT') {
                        $this->tokens[] = new Token($type, $value);
                    }

                    $this->pos += strlen($value);
                    break;
                }
            }

            if (!$matched) {
                throw new Exception("Token inválido na posição {$this->pos}: '{$this->source[$this->pos]}'");
            }
        }

        return $this->tokens;
    }
}
