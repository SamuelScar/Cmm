<?php

namespace Compiler;
class Lexer
{
    private string $source;
    private int $pos = 0;
    private int $length;
    private int $line = 1;
    private int $column = 1;

    private array $tokens = [];
    private array $errors = [];

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

                    $this->updatePosition($value);
                    break;
                }
            }

            if (!$matched) {
                $invalidChar = $this->source[$this->pos];
                $this->errors[] = [
                    'linha' => $this->line,
                    'coluna' => $this->column,
                    'simbolo' => $invalidChar,
                    'mensagem' => "Símbolo inválido encontrado: '{$invalidChar}'"
                ];
                $this->advance();
            }
        }

        return $this->tokens;
    }

    private function updatePosition(string $text): void
    {
        for ($i = 0; $i < strlen($text); $i++) {
            if ($text[$i] === "\n") {
                $this->line++;
                $this->column = 1;
            } else {
                $this->column++;
            }
            $this->pos++;
        }
    }

    private function advance(): void
    {
        if ($this->pos < $this->length) {
            if ($this->source[$this->pos] === "\n") {
                $this->line++;
                $this->column = 1;
            } else {
                $this->column++;
            }
            $this->pos++;
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
