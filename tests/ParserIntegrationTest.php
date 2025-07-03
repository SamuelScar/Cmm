<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use Compiler\Lexer;
use Compiler\Parser;
use Compiler\Node\ProgramNode;
use Compiler\SyntaxError;

class ParserIntegrationTest extends TestCase
{
    #[Test]
    #[DataProvider('validPrograms')]
    public function testParsesValidPrograms(string $filename): void
    {
        $code   = file_get_contents($filename);
        $tokens = (new Lexer($code))->tokenize();

        if ($_ENV['DEBUG_TESTS'] ?? false) {
            echo "\n🔎 Testando: " . basename($filename) . PHP_EOL;
            foreach ($tokens as $i => $t) {
                echo str_pad($i, 2, ' ', STR_PAD_LEFT) . ': [' . $t->type . '] ' . $t->value . PHP_EOL;
            }
        }
        $ast    = (new Parser($tokens))->parseProgram();

        $this->assertInstanceOf(
            ProgramNode::class,
            $ast,
            "Falha ao processar sintaticamente " . basename($filename)
        );
    }

    #[Test]
    #[DataProvider('invalidPrograms')]
    public function testThrowsOnInvalidPrograms(string $filename): void
    {
        $this->expectException(SyntaxError::class);

        $code   = file_get_contents($filename);
        $tokens = (new Lexer($code))->tokenize();
        (new Parser($tokens))->parseProgram();
    }

    public static function validPrograms(): array
    {
        $dir   = __DIR__ . '/test_files/valid_files_sintatic';
        $files = glob("$dir/*.c") ?: [];
        return array_map(fn($f) => [$f], $files);
    }

    public static function invalidPrograms(): array
    {
        $dir   = __DIR__ . '/test_files/invalid_files_sintatic';
        $files = glob("$dir/*.c") ?: [];
        return array_map(fn($f) => [$f], $files);
    }
}
