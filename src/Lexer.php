<?php

namespace Compiler;

/**
 * Realiza a análise léxica do código-fonte, transformando em tokens.
 *
 * @package Compiler
 */
class Lexer
{
    /**
     * Fonte de código a ser lida.
     *
     * @var string
     */
    private string $source;

    /**
     * Posição atual no texto da fonte.
     *
     * @var int
     */
    private int $pos = 0;

    /**
     * Comprimento total da fonte.
     *
     * @var int
     */
    private int $length;

    /**
     * Linha atual para relatórios de erro.
     *
     * @var int
     */
    private int $line = 1;

    /**
     * Coluna atual para relatórios de erro.
     *
     * @var int
     */
    private int $column = 1;

    /**
     * Lista de tokens gerados.
     *
     * @var Token[]
     */
    private array $tokens = [];

    /**
     * Lista de erros léxicos encontrados.
     *
     * Cada erro contém 'linha', 'coluna', 'simbolo' e 'mensagem'.
     *
     * @var array[]
     */
    private array $errors = [];

    /**
     * Padrões regex para identificar cada tipo de token.
     *
     * @var string[]
     */
    private array $tokenPatterns = [
        'WHITESPACE'     => '/^\s+/',
        'COMMENT'        => '/^\/\/.*|^\/\*[\s\S]*?\*\//',
        'STRING_LITERAL' => '/^"([^"\\\\]|\\\\.)*"/',
        'CHAR_LITERAL'   => "/^'([^'\\\\]|\\\\.)'/",
        'INT'            => '/^\bint\b/',
        'FLOAT'          => '/^\bfloat\b/',
        'CHAR'           => '/^\bchar\b/',
        'VOID'           => '/^\bvoid\b/',
        'IF'             => '/^\bif\b/',
        'ELSE'           => '/^\belse\b/',
        'RETURN'         => '/^\breturn\b/',
        'WHILE'          => '/^\bwhile\b/',
        'FOR'            => '/^\bfor\b/',
        'BREAK'          => '/^\bbreak\b/',
        'CONTINUE'       => '/^\bcontinue\b/',
        'SWITCH'         => '/^\bswitch\b/',
        'CASE'           => '/^\bcase\b/',
        'DEFAULT'        => '/^\bdefault\b/',
        'IDENTIFIER'     => '/^[a-zA-Z_][a-zA-Z0-9_]*/',
        'NUMBER'         => '/^\d+(\.\d+)?/',
        'OPERATOR' => '/^(?:\|\||&&|==|!=|<=|>=|<|>|!|\+|-|\*|\/|=|%)/',
        'DELIMITER'      => '/^[()\[\]{};,:]/',
    ];

    /**
     * Construtor do lexer.
     *
     * @param string $source Código-fonte a ser tokenizado.
     */
    public function __construct(string $source)
    {
        $this->source = $source;
        $this->length = strlen($source);
    }

    /**
     * Executa a tokenização até o fim da fonte.
     *
     * @return Token[] Lista de tokens reconhecidos.
     */
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
                        $this->tokens[] = new Token($type, $value, $this->line, $this->column);
                    }

                    $this->updatePosition($value);
                    break;
                }
            }

            if (!$matched) {
                $invalidChar = $this->source[$this->pos];
                $this->errors[] = [
                    'linha'   => $this->line,
                    'coluna'  => $this->column,
                    'simbolo' => $invalidChar,
                    'mensagem' => "Símbolo inválido encontrado: '{$invalidChar}'"
                ];
                $this->advance();
            }
        }

        return $this->tokens;
    }

    /**
     * Atualiza a posição, linha e coluna de acordo com o texto consumido.
     *
     * @param string $text Texto que foi reconhecido e consumido.
     */
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

    /**
     * Avança uma posição na fonte, atualizando linha/coluna conforme necessário.
     */
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

    /**
     * Retorna os erros léxicos encontrados durante a tokenização.
     *
     * @return array[] Lista de erros com informações de linha, coluna e mensagem.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
