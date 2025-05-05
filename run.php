#!/usr/bin/env php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Compiler\Lexer;

if (php_sapi_name() === 'cli') {
    if (!isset($argv[1])) {
        echo "Uso: php run.php <arquivo.c>\n";
        exit(1);
    }

    $input = file_get_contents($argv[1]);
    if ($input === false) {
        echo "Erro ao ler o arquivo {$argv[1]}\n";
        exit(1);
    }

    $lexer = new Lexer($input);
    $tokens = $lexer->tokenize();

    // Exibe tokens
    foreach ($tokens as $token) {
        echo $token . PHP_EOL;
    }

    // Exibe erros léxicos, se houver
    $errors = $lexer->getErrors();
    if (!empty($errors)) {
        echo PHP_EOL . "Erros léxicos encontrados:" . PHP_EOL;
        foreach ($errors as $erro) {
            echo "Linha {$erro['linha']}, Coluna {$erro['coluna']}: {$erro['mensagem']}" . PHP_EOL;
        }
    }
}
