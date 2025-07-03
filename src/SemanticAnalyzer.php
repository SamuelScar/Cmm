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

    private function generateBlockName(): string
    {
        return 'block' . (++$this->blockCounter);
    }

    private function enterScope(string $name): void
    {
        $this->scopeStack[]       = $name;
        $this->symbolTable[$name] = [];
    }

    private function exitScope(): void
    {
        array_pop($this->scopeStack);
    }

    private function getCurrentScope(): string
    {
        return end($this->scopeStack);
    }

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

    private function visitAssignment($node): void
    {
        $name = $node->name;
        if (! $this->isDeclared($name)) {
            $this->errors[] =
                "Erro Semântico: variável '{$name}' usada antes de ser declarada no escopo atual.";
        }
        $this->visit($node->expression);
    }

    private function visitIf($node): void
    {
        $this->visit($node->condition);

        $this->enterScope($this->generateBlockName());
        foreach ($node->statements as $stmt) {
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

    private function visitBreak($node): void
    {
        if ($this->loopDepth === 0) {
            $this->errors[] = "Erro Semântico: 'break' usado fora de um laço.";
        }
    }

    private function visitContinue($node): void
    {
        if ($this->loopDepth === 0) {
            $this->errors[] = "Erro Semântico: 'continue' usado fora de um laço.";
        }
    }

    private function visitReturn($node): void
    {
        if ($node->expression !== null) {
            $this->visit($node->expression);
        }
    }

    private function visitExpressionStatement($node): void
    {
        $this->visit($node->expression);
    }

    private function visitBinaryOpNode($node): void
    {
        $this->visit($node->left);
        $this->visit($node->right);
    }

    private function visitUnaryOpNode($node): void
    {
        $this->visit($node->operand);
    }

    /**
     * Despacha nós de expressão para o método específico, se existir.
     * Filtra tudo que não for objeto para evitar ReflectionException.
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
