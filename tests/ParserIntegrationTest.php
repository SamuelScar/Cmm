<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use Compiler\Lexer;
use Compiler\Parser;
use Compiler\Node\ProgramNode;

class ParserIntegrationTest extends TestCase
{
    #[Test]
    #[DataProvider('validCodeProvider')]
    public function testParsesValidPrograms(string $filename): void
    {
        $code = file_get_contents($filename);
        $lexer = new Lexer($code);
        $tokens = $lexer->tokenize();

        if ($_ENV['DEBUG_TESTS'] ?? false) {
            echo "\n🔎 Testando: " . basename($filename) . PHP_EOL;
            foreach ($tokens as $i => $t) {
                echo str_pad($i, 2, ' ', STR_PAD_LEFT) . ': [' . $t->type . '] ' . $t->value . PHP_EOL;
            }
        }

        $parser = new Parser($tokens);
        $ast = $parser->parseProgram();

        $this->assertInstanceOf(
            ProgramNode::class,
            $ast,
            "Falha ao processar " . basename($filename)
        );
    }

    #[Test]
    #[DataProvider('invalidCodeProvider')]
    public function testThrowsOnInvalidPrograms(string $filename): void
    {
        $this->expectException(\Compiler\SyntaxError::class);

        $code = file_get_contents($filename);
        $lexer = new Lexer($code);
        $tokens = $lexer->tokenize();
        $parser = new Parser($tokens);
        $parser->parseProgram();
    }

    public static function validCodeProvider(): array
    {
        $path = realpath(__DIR__ . '/test_files/valid_files');
        $files = glob($path . '/*.c') ?: [];
        return array_map(fn($f) => [$f], $files);
    }

    public static function invalidCodeProvider(): array
    {
        $path = realpath(__DIR__ . '/test_files/invalid_files');
        $files = glob($path . '/*.c') ?: [];
        return array_map(fn($f) => [$f], $files);
    }
}
