<?php

namespace Compiler;

use Compiler\Node\ProgramNode;

/**
 * Classe responsável pela análise semântica do código‐fonte em CMM.
 * Verifica declarações, escopos, uso de variáveis, laços, retornos e expressões.
 */
class SemanticAnalyzer
{
    private array $symbolTable = [];
    private array $scopeStack  = [];
    private array $errors      = [];
    private int $blockCounter  = 0;
    private int $loopDepth     = 0;

    /**
     * Inicia a análise semântica do programa.
     *
     * @param ProgramNode $program Nó raiz da AST do programa.
     * @return array Lista de mensagens de erro semântico encontradas.
     */
    public function analyze(ProgramNode $program): array
    {
        $this->symbolTable = [];
        $this->scopeStack  = [];
        $this->errors      = [];

        $this->enterScope('global');
        $this->visitProgram($program);
        $this->exitScope();

        return $this->errors;
    }

    /**
     * Analisa todos os statements do programa.
     *
     * @param ProgramNode $program Nó do programa.
     */
    private function visitProgram(ProgramNode $program): void
    {
        foreach ($program->statements as $stmt) {
            if ($stmt instanceof \Compiler\Node\FunctionNode) {
                $this->visitFunction($stmt);
            } else {
                $this->visitStatement($stmt);
            }
        }
    }

    /**
     * Analisa uma função, adicionando seus parâmetros ao escopo e analisando seu corpo.
     *
     * @param \Compiler\Node\FunctionNode $node Nó da função.
     */
    private function visitFunction(\Compiler\Node\FunctionNode $node): void
    {
        $this->enterScope($node->name);
        foreach ($node->parameters as $param) {
            $this->symbolTable[$node->name][$param->name] = true;
        }
        foreach ($node->body->statements as $stmt) {
            $this->visitStatement($stmt);
        }
        $this->exitScope();
    }

    /**
     * Despacha o statement para o método de análise apropriado.
     *
     * @param mixed $stmt Statement a ser analisado.
     */
    private function visitStatement($stmt): void
    {
        if ($stmt instanceof \Compiler\Node\DeclarationNode) {
            $this->visitDeclaration($stmt);
        } elseif ($stmt instanceof \Compiler\Node\AssignmentNode) {
            $this->visitAssignment($stmt);
        } elseif ($stmt instanceof \Compiler\Node\IfNode) {
            $this->visitIf($stmt);
        } elseif ($stmt instanceof \Compiler\Node\WhileNode) {
            $this->visitWhile($stmt);
        } elseif ($stmt instanceof \Compiler\Node\ForNode) {
            $this->visitFor($stmt);
        } elseif ($stmt instanceof \Compiler\Node\BreakNode) {
            $this->visitBreak($stmt);
        } elseif ($stmt instanceof \Compiler\Node\ContinueNode) {
            $this->visitContinue($stmt);
        } elseif ($stmt instanceof \Compiler\Node\ReturnNode) {
            $this->visitReturn($stmt);
        } elseif ($stmt instanceof \Compiler\Node\ExpressionStatementNode) {
            $this->visitExpressionStatement($stmt);
        }
    }

    /**
     * Verifica se uma variável foi declarada em algum escopo acessível.
     *
     * @param string $name Nome da variável.
     * @return bool True se declarada, false caso contrário.
     */
    private function isDeclared(string $name): bool
    {
        for ($i = count($this->scopeStack) - 1; $i >= 0; $i--) {
            $scope = $this->scopeStack[$i];
            if (isset($this->symbolTable[$scope][$name])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Gera um nome único para blocos aninhados.
     *
     * @return string Nome do bloco.
     */
    private function generateBlockName(): string
    {
        return 'block' . (++$this->blockCounter);
    }

    /**
     * Entra em um novo escopo, adicionando-o à pilha de escopos.
     *
     * @param string $name Nome do escopo.
     */
    private function enterScope(string $name): void
    {
        $this->scopeStack[]       = $name;
        $this->symbolTable[$name] = [];
    }

    /**
     * Sai do escopo atual, removendo-o da pilha.
     */
    private function exitScope(): void
    {
        array_pop($this->scopeStack);
    }

    /**
     * Retorna o nome do escopo atual.
     *
     * @return string Nome do escopo atual.
     */
    private function getCurrentScope(): string
    {
        return end($this->scopeStack);
    }

    /**
     * Analisa uma declaração de variável, verificando duplicidade e analisando o inicializador.
     *
     * @param mixed $node Nó de declaração.
     */
    private function visitDeclaration($node): void
    {
        $scope = $this->getCurrentScope();
        $name  = $node->name;

        if (isset($this->symbolTable[$scope][$name])) {
            $this->errors[] = "Erro Semântico: variável '{$name}' já declarada no escopo '{$scope}'.";
        } else {
            $this->symbolTable[$scope][$name] = true;
        }

        if ($node->initializer !== null) {
            $this->visit($node->initializer);
        }
    }

    /**
     * Analisa uma atribuição, verificando se a variável foi declarada.
     *
     * @param mixed $node Nó de atribuição.
     */
    private function visitAssignment($node): void
    {
        $name = $node->name;
        if (! $this->isDeclared($name)) {
            $this->errors[] =
                "Erro Semântico: variável '{$name}' usada antes de ser declarada no escopo atual.";
        }
        $this->visit($node->expression);
    }

    /**
     * Analisa um comando if/else, criando escopos para os blocos then e else.
     *
     * @param mixed $node Nó do if.
     */
    private function visitIf($node): void
    {
        $this->visit($node->condition);

        $this->enterScope($this->generateBlockName());
        foreach ($node->thenBranch->statements as $stmt) {
            $this->visitStatement($stmt);
        }
        $this->exitScope();

        if ($node->elseBranch !== null) {
            $this->enterScope($this->generateBlockName());
            foreach ($node->elseBranch->statements as $stmt) {
                $this->visitStatement($stmt);
            }
            $this->exitScope();
        }
    }

    /**
     * Analisa um laço while, criando escopo para o corpo e controlando profundidade de laço.
     *
     * @param mixed $node Nó do while.
     */
    private function visitWhile($node): void
    {
        $this->visit($node->condition);

        $this->loopDepth++;
        $this->enterScope($this->generateBlockName());
        foreach ($node->body->statements as $stmt) {
            $this->visitStatement($stmt);
        }
        $this->exitScope();
        $this->loopDepth--;
    }

    /**
     * Analisa um laço for, criando escopo para o corpo e controlando profundidade de laço.
     *
     * @param mixed $node Nó do for.
     */
    private function visitFor($node): void
    {
        $this->loopDepth++;
        $this->enterScope($this->generateBlockName());

        if ($node->init      !== null) { $this->visit($node->init); }
        if ($node->condition !== null) { $this->visit($node->condition); }
        if ($node->post      !== null) { $this->visit($node->post); }

        foreach ($node->body->statements as $stmt) {
            $this->visitStatement($stmt);
        }

        $this->exitScope();
        $this->loopDepth--;
    }

    /**
     * Analisa um comando break, verificando se está dentro de um laço.
     *
     * @param mixed $node Nó do break.
     */
    private function visitBreak($node): void
    {
        if ($this->loopDepth === 0) {
            $this->errors[] = "Erro Semântico: 'break' usado fora de um laço.";
        }
    }

    /**
     * Analisa um comando continue, verificando se está dentro de um laço.
     *
     * @param mixed $node Nó do continue.
     */
    private function visitContinue($node): void
    {
        if ($this->loopDepth === 0) {
            $this->errors[] = "Erro Semântico: 'continue' usado fora de um laço.";
        }
    }

    /**
     * Analisa um comando return, analisando a expressão de retorno se existir.
     *
     * @param mixed $node Nó do return.
     */
    private function visitReturn($node): void
    {
        if ($node->expression !== null) {
            $this->visit($node->expression);
        }
    }

    /**
     * Analisa um statement de expressão.
     *
     * @param mixed $node Nó de expressão.
     */
    private function visitExpressionStatement($node): void
    {
        $this->visit($node->expression);
    }

    /**
     * Analisa uma expressão binária, visitando ambos os operandos.
     *
     * @param mixed $node Nó binário.
     */
    private function visitBinaryOpNode($node): void
    {
        $this->visit($node->left);
        $this->visit($node->right);
    }

    /**
     * Analisa uma expressão unária, visitando o operando.
     *
     * @param mixed $node Nó unário.
     */
    private function visitUnaryOpNode($node): void
    {
        $this->visit($node->operand);
    }

    /**
     * Despacha nós de expressão para o método específico, se existir.
     * Filtra tudo que não for objeto para evitar ReflectionException.
     *
     * @param mixed $node Nó da AST a ser analisado.
     */
    private function visit($node): void
    {
        if (!is_object($node)) {
            return;
        }

        $short  = (new \ReflectionClass($node))->getShortName();
        $method = 'visit' . $short;

        if (method_exists($this, $method)) {
            $this->$method($node);
        }
    }
}
