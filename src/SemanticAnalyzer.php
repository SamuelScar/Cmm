<?php

namespace Compiler;

use Compiler\Node\ProgramNode;

/**
 * Classe responsável pela análise semântica do código-fonte em CMM.
 * Verifica declarações, escopos, uso de variáveis e validações de laços.
 */
class SemanticAnalyzer
{
    private array $symbolTable = [];
    private array $scopeStack = [];
    private array $errors = [];
    private int $blockCounter = 0;
    private int $loopDepth = 0;

    /**
     * Executa a análise semântica a partir da AST.
     *
     * @param ProgramNode $program
     * @return array Lista de erros semânticos encontrados.
     */
    public function analyze(ProgramNode $program): array
    {
        $this->symbolTable = [];
        $this->scopeStack = ['global'];
        $this->errors = [];

        $this->visitProgram($program);

        return $this->errors;
    }

    /**
     * Visita o nó principal do programa e percorre todos os statements.
     *
     * @param ProgramNode $program
     */
    private function visitProgram(ProgramNode $program): void
    {
        foreach ($program->statements as $statement) {
            $this->visitStatement($statement);
        }
    }

    /**
     * Visita um statement individual e direciona para o método correspondente.
     *
     * @param mixed $statement
     */
    private function visitStatement($statement): void
    {
        if ($statement instanceof \Compiler\Node\DeclarationNode) {
            $this->visitDeclaration($statement);
        } elseif ($statement instanceof \Compiler\Node\AssignmentNode) {
            $this->visitAssignment($statement);
        } elseif ($statement instanceof \Compiler\Node\IfNode) {
            $this->visitIf($statement);
        } elseif ($statement instanceof \Compiler\Node\WhileNode) {
            $this->visitWhile($statement);
        } elseif ($statement instanceof \Compiler\Node\ForNode) {
            $this->visitFor($statement);
        } elseif ($statement instanceof \Compiler\Node\BreakNode) {
            $this->visitBreak($statement);
        } elseif ($statement instanceof \Compiler\Node\ContinueNode) {
            $this->visitContinue($statement);
        }
    }

    /**
     * Verifica se uma variável está declarada em algum escopo visível.
     *
     * @param string $identifier
     * @return bool
     */
    private function isDeclared(string $identifier): bool
    {
        for ($i = count($this->scopeStack) - 1; $i >= 0; $i--) {
            $scope = $this->scopeStack[$i];
            if (isset($this->symbolTable[$scope][$identifier])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Gera um nome único para cada novo bloco de escopo.
     *
     * @return string
     */
    private function generateBlockName(): string
    {
        $this->blockCounter++;
        return "block{$this->blockCounter}";
    }

    /**
     * Entra em um novo escopo.
     *
     * @param string $scopeName
     */
    private function enterScope(string $scopeName): void
    {
        $this->scopeStack[] = $scopeName;
        $this->symbolTable[$scopeName] = [];
    }

    /**
     * Sai do escopo atual.
     */
    private function exitScope(): void
    {
        array_pop($this->scopeStack);
    }

    /**
     * Retorna o nome do escopo atual.
     *
     * @return string
     */
    private function getCurrentScope(): string
    {
        return end($this->scopeStack);
    }

    /**
     * Visita uma declaração de variáveis e atualiza a tabela de símbolos.
     *
     * @param mixed $node
     */
    private function visitDeclaration($node): void
    {
        $currentScope = $this->getCurrentScope();

        foreach ($node->identifiers as $identifier) {
            if (isset($this->symbolTable[$currentScope][$identifier])) {
                $this->errors[] = "Erro Semântico: Variável '{$identifier}' já declarada no escopo '{$currentScope}'.";
            } else {
                $this->symbolTable[$currentScope][$identifier] = 'int';
            }
        }
    }

    /**
     * Visita uma atribuição e verifica se a variável foi previamente declarada.
     *
     * @param mixed $node
     */
    private function visitAssignment($node): void
    {
        $identifier = $node->name;

        if (!$this->isDeclared($identifier)) {
            $this->errors[] = "Erro Semântico: Variável '{$identifier}' usada antes de ser declarada no escopo atual.";
        }

        // Mais pra frente: validar o lado direito da expressão se quiser
    }

    /**
     * Visita um bloco if, criando um novo escopo para ele.
     *
     * @param mixed $node
     */
    private function visitIf($node): void
    {
        $scopeName = $this->generateBlockName();
        $this->enterScope($scopeName);

        foreach ($node->statements as $statement) {
            $this->visitStatement($statement);
        }

        $this->exitScope();
    }

    /**
     * Visita um laço while, controlando escopo e profundidade de loop.
     *
     * @param mixed $node
     */
    private function visitWhile($node): void
    {
        $this->loopDepth++;

        $scopeName = $this->generateBlockName();
        $this->enterScope($scopeName);

        foreach ($node->body->statements as $statement) {
            $this->visitStatement($statement);
        }

        $this->exitScope();
        $this->loopDepth--;
    }

    /**
     * Visita um laço for, controlando escopo e profundidade de loop.
     *
     * @param mixed $node
     */
    private function visitFor($node): void
    {
        $this->loopDepth++;

        $scopeName = $this->generateBlockName();
        $this->enterScope($scopeName);

        foreach ($node->statements as $statement) {
            $this->visitStatement($statement);
        }

        $this->exitScope();
        $this->loopDepth--;
    }

    /**
     * Verifica se o uso de break está dentro de um loop válido.
     *
     * @param mixed $node
     */
    private function visitBreak($node): void
    {
        if ($this->loopDepth === 0) {
            $this->errors[] = "Erro Semântico: 'break' usado fora de um laço.";
        }
    }

    /**
     * Verifica se o uso de continue está dentro de um loop válido.
     *
     * @param mixed $node
     */
    private function visitContinue($node): void
    {
        if ($this->loopDepth === 0) {
            $this->errors[] = "Erro Semântico: 'continue' usado fora de um laço.";
        }
    }
}
