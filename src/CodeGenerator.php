<?php

namespace Compiler;

use Compiler\Node\ProgramNode;
use Compiler\Node\ReturnNode;
use Compiler\Node\NumberNode;
use Compiler\Node\BinaryOpNode;

class CodeGenerator
{
    private array $lines = [];

    public function generate(ProgramNode $node): string
    {
        $this->emit("section .text");
        $this->emit("global main");
        $this->emit("");
        $this->emit("main:");

        foreach ($node->statements as $stmt) {
            $this->visit($stmt);
        }

        $this->emit("    mov eax, 0");
        $this->emit("    ret");

        return implode("\n", $this->lines);
    }

    private function emit(string $line): void
    {
        $this->lines[] = $line;
    }

    private function visit($node): void
    {
        $method = 'visit' . (new \ReflectionClass($node))->getShortName();
        if (!method_exists($this, $method)) {
            throw new \Exception("Geração de código não implementada para: " . get_class($node));
        }
        $this->$method($node);
    }

    private function visitReturnNode(ReturnNode $node): void
    {
        $this->visit($node->expression);
        $this->emit("    ret");
    }

    private function visitNumberNode(NumberNode $node): void
    {
        $this->emit("    mov eax, {$node->value}");
    }

    private function visitBinaryOpNode(BinaryOpNode $node): void
    {
        $this->visit($node->left);
        $this->emit("    push rax");

        $this->visit($node->right);
        $this->emit("    pop rbx");

        switch ($node->op) {
            case '+': $this->emit("    add eax, ebx"); break;
            case '-': $this->emit("    sub eax, ebx"); break;
            case '*': $this->emit("    imul eax, ebx"); break;
            case '/':
                $this->emit("    xor edx, edx");
                $this->emit("    mov eax, ebx");
                $this->emit("    pop rcx"); // divisor
                $this->emit("    div ecx");
                break;
            default:
                throw new \Exception("Operador não suportado: " . $node->op);
        }
    }

    private function visitFunctionNode(\Compiler\Node\FunctionNode $node): void
    {
        if ($node->name !== 'main') {
            throw new \Exception("Apenas a função 'main' é suportada no momento.");
        }

        $this->emit("main:");

        foreach ($node->body->statements as $stmt) {
            $this->visit($stmt);
        }
    }

}
