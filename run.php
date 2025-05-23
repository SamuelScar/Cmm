#!/usr/bin/env php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Compiler\Lexer;
use Compiler\Parser;

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

echo "\n=== PARSING ===\n";
$parser = new Parser($tokens);

try {
    $ast = $parser->parseProgram();
    echo "Árvore de sintaxe (AST):\n";
    var_dump($ast);
} catch (\Exception $e) {
    echo "Erro sintático: ", $e->getMessage(), PHP_EOL;
    exit(1);
}
