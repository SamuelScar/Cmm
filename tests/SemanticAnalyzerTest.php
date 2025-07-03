<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use Compiler\Lexer;
use Compiler\Parser;
use Compiler\SemanticAnalyzer;
use Compiler\SyntaxError;

class SemanticAnalyzerTest extends TestCase
{
    private function analyzeFile(string $filename): array
    {
        $code   = file_get_contents($filename);
        $tokens = (new Lexer($code))->tokenize();
        $ast    = (new Parser($tokens))->parseProgram();
        return (new SemanticAnalyzer())->analyze($ast);
    }

    #[Test]
    #[DataProvider('validSemanticPrograms')]
    public function testValidSemanticPrograms(string $filename): void
    {
        if (($_ENV['DEBUG_TESTS'] ?? false)) {
            echo "\n🔎 [SEMÂNTICO-OK] Testando: " . basename($filename) . PHP_EOL;
        }

        $errors = $this->analyzeFile($filename);
        $this->assertEmpty(
            $errors,
            "Falha semântica inesperada em: " . basename($filename) . "\n" . implode("\n", $errors)
        );
    }

    #[Test]
    #[DataProvider('invalidSemanticPrograms')]
    public function testInvalidSemanticPrograms(string $filename): void
    {
        if (($_ENV['DEBUG_TESTS'] ?? false)) {
            echo "\n🔎 [SEMÂNTICO-ERR] Testando: " . basename($filename) . PHP_EOL;
        }

        try {
            $errors = $this->analyzeFile($filename);
            $this->assertNotEmpty(
                $errors,
                "Esperado erro semântico em: " . basename($filename)
            );
        } catch (SyntaxError $_) {
            $this->assertTrue(true);
        }
    }

    public static function validSemanticPrograms(): array
    {
        $dir   = __DIR__ . '/test_files/valid_files_semantic';
        $files = glob("$dir/*.c") ?: [];
        return array_map(fn($f) => [$f], $files);
    }

    public static function invalidSemanticPrograms(): array
    {
        $dir   = __DIR__ . '/test_files/invalid_files_semantic';
        $files = glob("$dir/*.c") ?: [];
        return array_map(fn($f) => [$f], $files);
    }
}
