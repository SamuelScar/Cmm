<?php

namespace Compiler\Node;

/**
 * Representa uma chamada de função na AST.
 *
 * @package Compiler\Node
 */
class FunctionCallNode extends ExpressionNode
{
    /**
     * Nome da função a ser chamada.
     *
     * @var string
     */
    private string $name;

    /**
     * Lista de argumentos passados para a função.
     *
     * @var ExpressionNode[]
     */
    private array $arguments;

    /**
     * Construtor do nó de chamada de função.
     *
     * @param string              $name       Nome da função.
     * @param ExpressionNode[]    $arguments  Argumentos da chamada (padrão: vazio).
     */
    public function __construct(string $name, array $arguments = [])
    {
        $this->name      = $name;
        $this->arguments = $arguments;
    }

    /**
     * Retorna o nome da função chamada.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Retorna a lista de argumentos da chamada de função.
     *
     * @return ExpressionNode[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }
}
