#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * run.php
 *
 * Ponto de entrada para o compilador C em PHP via linha de comando.
 * Integra fases de análise léxica, sintática, semântica e geração de assembly.
 *
 * Uso:
 *   php run.php <arquivo.c>
 */

require __DIR__ . '/vendor/autoload.php';

use Compiler\Lexer;
use Compiler\Parser;
use Compiler\SemanticAnalyzer;
use Compiler\CodeGenerator;
use Compiler\SyntaxError;
use Dotenv\Dotenv;

/**
 * Carrega as variáveis de ambiente definidas em .env.
 */
function loadEnvironment(): void
{
    Dotenv::createImmutable(__DIR__)->load();
}

/**
 * Valida e retorna o nome do arquivo fonte passado por argumento.
 * Encerra o script com código de erro se a execução não for via CLI
 * ou se nenhum arquivo for informado.
 *
 * @return string Caminho do arquivo fonte .c
 */
function parseArguments(): string
{
    global $argv;
    if (php_sapi_name() !== 'cli' || empty($argv[1])) {
        echo "Uso: php run.php <arquivo.c>" . PHP_EOL;
        exit(1);
    }
    return $argv[1];
}

/**
 * Lê o conteúdo do arquivo fonte.
 * Exibe mensagem de erro e encerra se não for possível ler.
 *
 * @param string $filename Caminho do arquivo .c
 * @return string Conteúdo do arquivo
 */
function readSource(string $filename): string
{
    $content = @file_get_contents($filename);
    if ($content === false) {
        echo "Erro ao ler o arquivo: {$filename}" . PHP_EOL;
        exit(1);
    }
    return $content;
}

/**
 * Acrescenta uma seção de log ao buffer principal.
 *
 * @param string $log Referência ao buffer de log
 * @param string $section Título da seção (ex: TOKENS, AST)
 * @param string $text   Conteúdo a ser registrado
 */
function appendLog(string &$log, string $section, string $text): void
{
    $log .= "=== {$section} ===" . PHP_EOL . $text . PHP_EOL;
}

/**
 * Gera a saída final: imprime no console e/ou grava em arquivos
 * conforme flags definidas.
 *
 * @param string $log        Conteúdo completo de log
 * @param string $assembly   Código assembly gerado
 * @param string $base       Nome-base do arquivo fonte (sem extensão)
 * @param bool   $consoleLog Se deve imprimir o log no console
 * @param bool   $writeLog   Se deve gravar o log em arquivo
 * @param bool   $writeAsm   Se deve gravar o ASM em arquivo
 */
function outputAndExit(
    string $log,
    string $assembly,
    string $base,
    bool $consoleLog,
    bool $writeLog,
    bool $writeAsm
): void {
    $root = __DIR__ . '/output';
    if (!is_dir($root) && ($consoleLog || $writeLog || $writeAsm)) {
        mkdir($root, 0755, true);
    }
    if ($writeLog) {
        $path = "{$root}/log";
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        file_put_contents("{$path}/{$base}.log", $log);
        echo "[OK] Log gravado em: {$path}/{$base}.log" . PHP_EOL;
    }
    if ($consoleLog) {
        echo $log;
    }
    if ($writeAsm) {
        $path = "{$root}/assembly";
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        file_put_contents("{$path}/{$base}.asm", $assembly);
        echo "[OK] ASM gravado em: {$path}/{$base}.asm" . PHP_EOL;
    }
    exit(0);
}

/**
 * Função principal: orquestra o fluxo de trabalho do compilador.
 */
function main(): void
{
    loadEnvironment();
    $sourceFile = parseArguments();
    $source     = readSource($sourceFile);

    $consoleLog = filter_var($_ENV['CONSOLE_LOG'] ?? false, FILTER_VALIDATE_BOOLEAN);
    $writeLog   = filter_var($_ENV['WRITE_LOG']   ?? false, FILTER_VALIDATE_BOOLEAN);
    $writeAsm   = filter_var($_ENV['WRITE_ASM']   ?? false, FILTER_VALIDATE_BOOLEAN);

    $log = '';

    // Análise Léxica
    $lexer    = new Lexer($source);
    $tokens   = $lexer->tokenize();
    appendLog($log, 'TOKENS', implode(PHP_EOL, $tokens));
    $errors   = $lexer->getErrors();
    if (!empty($errors)) {
        $msgs = array_map(
            fn(array $e) => "Linha {$e['linha']}, Col {$e['coluna']}: {$e['mensagem']}",
            $errors
        );
        appendLog($log, 'ERROS LÉXICOS', implode(PHP_EOL, $msgs));
        outputAndExit($log, '', pathinfo($sourceFile, PATHINFO_FILENAME), $consoleLog, $writeLog, $writeAsm);
    }

    // Análise Sintática
    $parser = new Parser($tokens);
    try {
        $ast = $parser->parseProgram();
        appendLog($log, 'AST', var_export($ast, true));
    } catch (SyntaxError $e) {
        appendLog($log, 'ERRO SINTÁTICO', $e->getMessage());
        outputAndExit($log, '', pathinfo($sourceFile, PATHINFO_FILENAME), $consoleLog, $writeLog, $writeAsm);
    }

    // Análise Semântica
    $analyzer       = new SemanticAnalyzer();
    $semanticErrors = $analyzer->analyze($ast);
    if (!empty($semanticErrors)) {
        appendLog($log, 'ERROS SEMÂNTICOS', implode(PHP_EOL, $semanticErrors));
        outputAndExit($log, '', pathinfo($sourceFile, PATHINFO_FILENAME), $consoleLog, $writeLog, $writeAsm);
    }
    appendLog($log, 'SEMÂNTICO', 'Nenhum erro semântico encontrado.');

    // Geração de Assembly
    $generator  = new CodeGenerator();
    $assembly   = $generator->generate($ast);

    outputAndExit(
        $log,
        $assembly,
        pathinfo($sourceFile, PATHINFO_FILENAME),
        $consoleLog,
        $writeLog,
        $writeAsm
    );
}

main();
