<?php

namespace Compiler;

use Compiler\Node\ProgramNode;
use Compiler\Node\ReturnNode;
use Compiler\Node\NumberNode;
use Compiler\Node\BinaryOpNode;
use Compiler\Node\FunctionNode;
use Compiler\Node\AssignmentNode;
use Compiler\Node\BlockNode;
use Compiler\Node\BreakNode;
use Compiler\Node\ContinueNode;
use Compiler\Node\DeclarationNode;
use Compiler\Node\ForNode;
use Compiler\Node\FunctionCallNode;
use Compiler\Node\IdentifierNode;
use Compiler\Node\IfNode;
use Compiler\Node\SwitchNode;
use Compiler\Node\WhileNode;

/**
 * Classe responsável por gerar código assembly x86-64 a partir da AST do CMM.
 * Cada método 'visit' gera código para um tipo de nó da AST.
 */
class CodeGenerator
{
    private array $lines = [];
    private array $localVars = [];
    private int $nextOffset = -4;
    private array $loopStartLabels = [];
    private array $loopEndLabels = [];
    private array $switchEndLabels = [];
    private int $labelCounter = 0;

    /**
     * Gera o código assembly para o programa.
     *
     * @param ProgramNode $node Nó raiz do programa.
     * @return string Código assembly gerado.
     */
    public function generate(ProgramNode $node): string
    {
        $this->emit("section .text");
        $this->emit("global main");
        $this->emit("");
        foreach ($node->statements as $stmt) {
            $this->visit($stmt);
        }
        return implode("\n", $this->lines);
    }

    /**
     * Adiciona uma linha ao código gerado.
     *
     * @param string $line Linha de código assembly.
     */
    private function emit(string $line): void
    {
        $this->lines[] = $line;
    }

    /**
     * Despacha o nó para o método visit correspondente.
     *
     * @param mixed $node Nó da AST.
     * @throws \Exception Se não houver método visit para o tipo do nó.
     */
    private function visit($node): void
    {
        $method = 'visit' . (new \ReflectionClass($node))->getShortName();
        if (!method_exists($this, $method)) {
            throw new \Exception("Geração de código não implementada para: " . get_class($node));
        }
        $this->$method($node);
    }

    /**
     * Gera código para definição de função.
     *
     * @param FunctionNode $node Nó da função.
     */
    private function visitFunctionNode(FunctionNode $node): void
    {
        $name = $node->name;
        $this->emit("{$name}:");
        $this->emit("    push rbp");
        $this->emit("    mov rbp, rsp");
        $this->emit("    sub rsp, 16");
        $this->localVars = [];
        $this->nextOffset = -4;
        foreach ($node->body->statements as $stmt) {
            $this->visit($stmt);
        }
        $this->emit("    mov rsp, rbp");
        $this->emit("    pop rbp");
        $this->emit("    ret");
    }

    /**
     * Gera código para comando return.
     *
     * @param ReturnNode $node Nó do return.
     */
    private function visitReturnNode(ReturnNode $node): void
    {
        $this->visit($node->expression);
        $this->emit("    mov rsp, rbp");
        $this->emit("    pop rbp");
        $this->emit("    ret");
    }

    /**
     * Gera código para um literal numérico.
     *
     * @param NumberNode $node Nó do número.
     */
    private function visitNumberNode(NumberNode $node): void
    {
        $this->emit("    mov eax, {$node->value}");
    }

    /**
     * Gera código para uso de variável (identificador).
     *
     * @param IdentifierNode $node Nó do identificador.
     */
    private function visitIdentifierNode(IdentifierNode $node): void
    {
        $offset = $this->getVariableOffset($node->name);
        $this->emit("    mov eax, [rbp{$offset}]");
    }

    /**
     * Gera código para atribuição de variável.
     *
     * @param AssignmentNode $node Nó da atribuição.
     */
    private function visitAssignmentNode(AssignmentNode $node): void
    {
        $this->visit($node->expression);
        $offset = $this->getVariableOffset($node->name);
        $this->emit("    mov [rbp{$offset}], eax");
    }

    /**
     * Retorna o deslocamento da variável na pilha, criando se necessário.
     *
     * @param string $name Nome da variável.
     * @return string Deslocamento em relação ao rbp.
     */
    private function getVariableOffset(string $name): string
    {
        if (!isset($this->localVars[$name])) {
            $this->localVars[$name] = $this->nextOffset;
            $this->nextOffset -= 4;
        }
        $offset = $this->localVars[$name];
        return ($offset < 0) ? $offset : "+{$offset}";
    }

    /**
     * Gera código para declaração de variável (com ou sem inicialização).
     *
     * @param DeclarationNode $node Nó da declaração.
     */
    private function visitDeclarationNode(DeclarationNode $node): void
    {
        if ($node->initializer !== null) {
            $this->visit($node->initializer);
            $offset = $this->getVariableOffset($node->name);
            $this->emit("    mov [rbp{$offset}], eax");
        } else {
            $this->getVariableOffset($node->name);
        }
    }

    /**
     * Gera código para operações binárias (+, -, *, /, etc).
     *
     * @param BinaryOpNode $node Nó da operação binária.
     * @throws \Exception Se o operador não for suportado.
     */
    private function visitBinaryOpNode(BinaryOpNode $node): void
    {
        $this->visit($node->left);
        $this->emit("    push rax");
        $this->visit($node->right);
        $this->emit("    pop rbx");
        switch ($node->op) {
            case '+':
                $this->emit("    add eax, ebx");
                break;
            case '-':
                $this->emit("    sub eax, ebx");
                break;
            case '*':
                $this->emit("    imul eax, ebx");
                break;
            case '/':
                $this->emit("    mov ecx, ebx");
                $this->emit("    cdq");
                $this->emit("    idiv ecx");
                break;
            case '%':
                $this->emit("    mov ecx, ebx");
                $this->emit("    cdq");
                $this->emit("    idiv ecx");
                $this->emit("    mov eax, edx");
                break;
            case '<':
                $this->emit("    cmp eax, ebx");
                $this->emit("    setl al");
                $this->emit("    movzx eax, al");
                break;
            case '<=':
                $this->emit("    cmp eax, ebx");
                $this->emit("    setle al");
                $this->emit("    movzx eax, al");
                break;
            case '>':
                $this->emit("    cmp eax, ebx");
                $this->emit("    setg al");
                $this->emit("    movzx eax, al");
                break;
            case '>=':
                $this->emit("    cmp eax, ebx");
                $this->emit("    setge al");
                $this->emit("    movzx eax, al");
                break;
            case '==':
                $this->emit("    cmp eax, ebx");
                $this->emit("    sete al");
                $this->emit("    movzx eax, al");
                break;
            case '!=':
                $this->emit("    cmp eax, ebx");
                $this->emit("    setne al");
                $this->emit("    movzx eax, al");
                break;
            case '&&':
                $this->emit("    and eax, ebx");
                $this->emit("    setne al");
                $this->emit("    movzx eax, al");
                break;
            case '||':
                $this->emit("    or eax, ebx");
                $this->emit("    setne al");
                $this->emit("    movzx eax, al");
                break;
            default:
                throw new \Exception("Operador não suportado: " . $node->op);
        }
    }

    /**
     * Gera um label único para uso em saltos e blocos.
     *
     * @param string $prefix Prefixo do label.
     * @return string Label gerado.
     */
    private function generateLabel(string $prefix): string
    {
        return "{$prefix}_" . (++$this->labelCounter);
    }

    /**
     * Gera código para laço for.
     *
     * @param ForNode $node Nó do for.
     */
    private function visitForNode(ForNode $node): void
    {
        $startLabel = $this->generateLabel("for_start");
        $endLabel   = $this->generateLabel("for_end");
        $this->loopStartLabels[] = $startLabel;
        $this->loopEndLabels[]   = $endLabel;
        if ($node->init !== null) {
            $this->visit($node->init);
        }
        $this->emit("{$startLabel}:");
        if ($node->condition !== null) {
            $this->visit($node->condition);
            $this->emit("    cmp eax, 0");
            $this->emit("    je {$endLabel}");
        }
        $this->visit($node->body);
        if ($node->post !== null) {
            $this->visit($node->post);
        }
        $this->emit("    jmp {$startLabel}");
        $this->emit("{$endLabel}:");
        array_pop($this->loopStartLabels);
        array_pop($this->loopEndLabels);
    }

    /**
     * Gera código para laço while.
     *
     * @param WhileNode $node Nó do while.
     */
    private function visitWhileNode(WhileNode $node): void
    {
        $startLabel = $this->generateLabel("while_start");
        $endLabel   = $this->generateLabel("while_end");
        $this->loopStartLabels[] = $startLabel;
        $this->loopEndLabels[]   = $endLabel;
        $this->emit("{$startLabel}:");
        $this->visit($node->condition);
        $this->emit("    cmp eax, 0");
        $this->emit("    je {$endLabel}");
        $this->visit($node->body);
        $this->emit("    jmp {$startLabel}");
        $this->emit("{$endLabel}:");
        array_pop($this->loopStartLabels);
        array_pop($this->loopEndLabels);
    }

    /**
     * Gera código para blocos de statements.
     *
     * @param BlockNode $node Nó do bloco.
     */
    private function visitBlockNode(BlockNode $node): void
    {
        foreach ($node->statements as $stmt) {
            $this->visit($stmt);
        }
    }

    /**
     * Gera código para comandos if/else.
     *
     * @param IfNode $node Nó do if.
     */
    private function visitIfNode(IfNode $node): void
    {
        $elseLabel = $this->generateLabel("else");
        $endLabel  = $this->generateLabel("endif");
        $this->visit($node->condition);
        $this->emit("    cmp eax, 0");
        $this->emit("    je {$elseLabel}");
        $this->visit($node->thenBranch);
        $this->emit("    jmp {$endLabel}");
        $this->emit("{$elseLabel}:");
        if ($node->elseBranch !== null) {
            $this->visit($node->elseBranch);
        }
        $this->emit("{$endLabel}:");
    }

    /**
     * Gera código para comando continue.
     *
     * @param ContinueNode $node Nó do continue.
     * @throws \Exception Se usado fora de um loop.
     */
    private function visitContinueNode(ContinueNode $node): void
    {
        if (empty($this->loopStartLabels)) {
            throw new \Exception("'continue' usado fora de um loop");
        }
        $startLabel = end($this->loopStartLabels);
        $this->emit("    jmp {$startLabel}");
    }

    /**
     * Gera código para comando break.
     *
     * @param BreakNode $node Nó do break.
     * @throws \Exception Se usado fora de um loop ou switch.
     */
    private function visitBreakNode(BreakNode $node): void
    {
        if (!empty($this->loopEndLabels)) {
            $endLabel = end($this->loopEndLabels);
            $this->emit("    jmp {$endLabel}");
        } elseif (!empty($this->switchEndLabels)) {
            $endLabel = end($this->switchEndLabels);
            $this->emit("    jmp {$endLabel}");
        } else {
            throw new \Exception("'break' usado fora de um loop ou switch");
        }
    }

    /**
     * Gera código para chamada de função.
     *
     * @param FunctionCallNode $node Nó da chamada de função.
     * @throws \Exception Se houver mais de 6 argumentos.
     */
    private function visitFunctionCallNode(FunctionCallNode $node): void
    {
        $argRegisters = ['rdi', 'rsi', 'rdx', 'rcx', 'r8', 'r9'];
        $arguments = $node->getArguments();
        $argCount = count($arguments);
        if ($argCount > count($argRegisters)) {
            throw new \Exception("Mais de 6 argumentos não suportados ainda.");
        }
        foreach ($arguments as $i => $arg) {
            $this->visit($arg);
            $this->emit("    mov {$argRegisters[$i]}, eax");
        }
        $funcName = $node->getName();
        $this->emit("    call {$funcName}");
    }

    /**
     * Gera código para comando switch/case.
     *
     * @param SwitchNode $node Nó do switch.
     */
    private function visitSwitchNode(SwitchNode $node): void
    {
        $endLabel = $this->generateLabel("switch_end");
        $this->switchEndLabels[] = $endLabel;
        $this->visit($node->expression);
        $this->emit("    mov rbx, eax");
        $caseLabels = [];
        foreach ($node->cases as $index => $caseNode) {
            $label = $this->generateLabel("case_{$index}");
            $caseLabels[] = $label;
            $this->visit($caseNode->value);
            $this->emit("    cmp rbx, eax");
            $this->emit("    je {$label}");
        }
        if (!empty($node->defaultStatements)) {
            $defaultLabel = $this->generateLabel("default_case");
            $this->emit("    jmp {$defaultLabel}");
        } else {
            $this->emit("    jmp {$endLabel}");
        }
        foreach ($node->cases as $index => $caseNode) {
            $this->emit("{$caseLabels[$index]}:");
            foreach ($caseNode->statements as $stmt) {
                $this->visit($stmt);
            }
        }
        if (!empty($node->defaultStatements)) {
            $this->emit("{$defaultLabel}:");
            foreach ($node->defaultStatements as $stmt) {
                $this->visit($stmt);
            }
        }
        $this->emit("{$endLabel}:");
        array_pop($this->switchEndLabels);
    }
}
