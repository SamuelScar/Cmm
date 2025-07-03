#!/usr/bin/env php
<?php

/**
 * run.php
 *
 * Ponto de entrada da linha de comando para o compilador C em PHP.
 * Executa análise léxica, sintática, semântica e gera código assembly.
 *
 * Uso:
 *   php run.php <arquivo.c>
 */

require_once __DIR__ . '/vendor/autoload.php';

use Compiler\Lexer;
use Compiler\Parser;
use Compiler\SemanticAnalyzer;
use Compiler\CodeGenerator;
use Compiler\SyntaxError;

if (php_sapi_name() !== 'cli') {
    echo "Este script deve ser executado via CLI.\n";
    exit(1);
}

if (!isset($argv[1])) {
    echo "Uso: php run.php <arquivo.c>\n";
    exit(1);
}

$input = file_get_contents($argv[1]);
if ($input === false) {
    echo "Erro ao ler o arquivo {$argv[1]}\n";
    exit(1);
}

// === LÉXICO ===
$lexer  = new Lexer($input);
$tokens = $lexer->tokenize();

echo "=== TOKENS ===\n";
foreach ($tokens as $token) {
    echo $token, PHP_EOL;
}

$errors = $lexer->getErrors();
if (!empty($errors)) {
    echo "\n=== ERROS LÉXICOS ===\n";
    foreach ($errors as $err) {
        echo "Linha {$err['linha']}, Coluna {$err['coluna']}: {$err['mensagem']}\n";
    }
    exit(1);
}

// === SINTÁTICO ===
echo "\n=== PARSING ===\n";
$parser = new Parser($tokens);

try {
    $ast = $parser->parseProgram();
    echo "Árvore de sintaxe (AST):\n";
    var_dump($ast);
} catch (SyntaxError $e) {
    echo "Erro sintático:\n", $e->getMessage(), PHP_EOL;
    exit(1);
} catch (\Exception $e) {
    echo "Erro: ", $e->getMessage(), PHP_EOL;
    exit(1);
}

// === SEMÂNTICO ===
echo "\n=== ANÁLISE SEMÂNTICA ===\n";

$analyzer = new SemanticAnalyzer();
$semanticErrors = $analyzer->analyze($ast);

if (!empty($semanticErrors)) {
    echo "Erros semânticos encontrados:\n";
    foreach ($semanticErrors as $error) {
        echo "- {$error}\n";
    }
    exit(1);
}

echo "Nenhum erro semântico encontrado.\n";

// === GERAÇÃO DE CÓDIGO ===
echo "\n=== GERAÇÃO DE CÓDIGO ===\n";
$generator = new CodeGenerator();
$assembly = $generator->generate($ast);

file_put_contents('out.asm', $assembly);
echo "✅ Código assembly gerado com sucesso: out.asm\n";
