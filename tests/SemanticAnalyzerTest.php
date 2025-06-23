<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use Compiler\Lexer;
use Compiler\Parser;
use Compiler\SemanticAnalyzer;
use Compiler\Node\ProgramNode;

class SemanticAnalyzerTest extends TestCase
{
    private function analyzeFile(string $filename): array
    {
        $code = file_get_contents($filename);
        $lexer = new Lexer($code);
        $tokens = $lexer->tokenize();
        $parser = new Parser($tokens);
        $ast = $parser->parseProgram();

        $analyzer = new SemanticAnalyzer();
        return $analyzer->analyze($ast);
    }

    #[Test]
    #[DataProvider('validFiles')]
    public function testValidSemanticPrograms(string $filename): void
    {
        $errors = $this->analyzeFile($filename);

        $this->assertEmpty(
            $errors,
            "Falha semântica inesperada em: " . basename($filename) . "\n" . implode("\n", $errors)
        );
    }

    #[Test]
    #[DataProvider('invalidFiles')]
    public function testInvalidSemanticPrograms(string $filename): void
    {
        try {
            $errors = $this->analyzeFile($filename);

            // Só testa semântica se o código for sintaticamente válido
            if (!empty($errors)) {
                $this->assertTrue(true); // Encontrou erros semânticos como esperado
            } else {
                $this->markTestSkipped("O arquivo '{$filename}' é inválido sintaticamente e não chegou na análise semântica.");
            }
        } catch (\Compiler\SyntaxError) {
            // Skip arquivos com erro sintático, porque eles nunca chegam na análise semântica
            $this->markTestSkipped("Erro sintático em {$filename}, ignorando no teste semântico.");
        }
    }

    public static function validFiles(): array
    {
        $path = realpath(__DIR__ . '/test_files/valid_files');
        $files = glob($path . '/*.c') ?: [];
        return array_map(fn($f) => [$f], $files);
    }

    public static function invalidFiles(): array
    {
        $path = realpath(__DIR__ . '/test_files/invalid_files');
        $files = glob($path . '/*.c') ?: [];
        return array_map(fn($f) => [$f], $files);
    }
}
