<?php

namespace Compiler;

use Compiler\Token;
use Compiler\Node\ProgramNode;
use Compiler\Node\StatementNode;
use Compiler\Node\DeclarationNode;
use Compiler\Node\AssignmentNode;
use Compiler\Node\BinaryOpNode;
use Compiler\Node\ExpressionNode;
use Compiler\Node\NumberNode;
use Compiler\Node\IdentifierNode;
use Compiler\Node\IfNode;
use Compiler\Node\WhileNode;
use Compiler\Node\BlockNode;
use Compiler\Node\ForNode;
use Compiler\Node\ReturnNode;
use Compiler\Node\FunctionNode;
use Compiler\Node\ParameterNode;
use Compiler\Node\BreakNode;
use Compiler\Node\ContinueNode;
use Compiler\Node\CaseNode;
use Compiler\Node\SwitchNode;
use Compiler\Node\UnaryOpNode;
use Compiler\Node\FunctionCallNode;
use Compiler\Node\ExpressionStatementNode;
use Compiler\Node\StringLiteralNode;

/**
 * Parser recursivo-descendente para CMM (linguagem C subset).
 *
 * Recebe uma lista de tokens gerada pelo Lexer e produz a AST correspondente.
 * Suporta:
 *  - Declarações de variáveis e funções
 *  - Atribuições
 *  - Expressões completas (aritméticas, relacionais e lógicas) com precedência
 *  - Controle de fluxo: if/else, while, for, switch/case, break, continue
 *  - Chamadas de função com lista de argumentos
 *  - Literais de string e número
 *
 * @package Compiler
 */
class Parser
{
    /** 
     * Fila de tokens para parsing.  
     * @var Token[] 
     */
    private array $tokens;

    /** 
     * Índice do próximo token a ser processado em $tokens.  
     * @var int 
     */
    private int $pos = 0;

    /**
     * Inicializa o parser com a lista de tokens.
     *
     * @param Token[] $tokens Lista de tokens produzida pelo Lexer.
     */
    public function __construct(array $tokens)
    {
        $this->tokens = $tokens;
    }

    /**
     * Retorna o token atual da lista sem avançar o cursor.
     * Se não houver mais tokens, retorna um token EOF.
     *
     * @return Token Próximo token ou EOF.
     */
    private function lookahead(): Token
    {
        return $this->tokens[$this->pos] ?? new Token('EOF', '');
    }

    /**
     * Consome e retorna o próximo token, verificando seu tipo.
     * Lança exceção se o tipo do token não corresponder ao esperado.
     *
     * @param string $type Tipo de token esperado (por ex.: 'IDENTIFIER', 'DELIMITER').
     * @return Token Token consumido.
     * @throws \RuntimeException Em caso de token inesperado.
     */
    private function match(string $type): Token
    {
        $token = $this->lookahead();
        if ($token->type !== $type) {
            throw new \RuntimeException(sprintf(
                "Syntax error: esperado %s, encontrado %s at position %d",
                $type,
                $token->type,
                $this->pos
            ));
        }
        $this->pos++;
        return $token;
    }

    /**
     * Inicia o parsing do programa, consumindo tokens até EOF
     * e retornando um ProgramNode com a lista de statements.
     *
     * @return ProgramNode Nó raiz da AST contendo todos os statements.
     */
    public function parseProgram(): ProgramNode
    {
        $statements = [];
        while ($this->lookahead()->type !== 'EOF') {
            $statements[] = $this->parseStatement();
        }
        return new ProgramNode($statements);
    }

    /**
     * Dispatcher de statements: identifica o tipo de statement
     * com base no token atual e delega ao método de parsing apropriado.
     *
     * Suporta:
     *  - Declarações e definições de função
     *  - Atribuições e chamadas de função como expression statements
     *  - If, While, For, Return, Switch, Break, Continue
     *  - Blocos e statements vazios
     *
     * @return StatementNode Nó de statement parseado.
     * @throws \RuntimeException Em caso de token inesperado ou erro de sintaxe.
     */
    private function parseStatement(): StatementNode
    {
        $la = $this->lookahead();
        if (in_array($la->type, ['INT', 'FLOAT', 'CHAR', 'VOID'], true)) {
            $next  = $this->tokens[$this->pos + 1] ?? null;
            $next2 = $this->tokens[$this->pos + 2] ?? null;
            if (
                $next
                && $next->type === 'IDENTIFIER'
                && $next2
                && $next2->type === 'DELIMITER'
                && $next2->value === '('
            ) {
                return $this->parseFunctionDefinition();
            }
            return $this->parseDeclaration();
        }
        switch ($la->type) {
            case 'IDENTIFIER':
                $next = $this->tokens[$this->pos + 1] ?? null;
                if (
                    $next
                    && $next->type === 'DELIMITER'
                    && $next->value === '('
                ) {
                    $expr = $this->parseExpression();
                    $semi = $this->match('DELIMITER');
                    if ($semi->value !== ';') {
                        throw new \RuntimeException(
                            "Syntax error: esperava ';' após expressão, encontrou '{$semi->value}'"
                        );
                    }
                    return new ExpressionStatementNode($expr);
                }
                return $this->parseAssignment();
            case 'IF':
                return $this->parseIfStatement();
            case 'WHILE':
                return $this->parseWhileStatement();
            case 'FOR':
                return $this->parseForStatement();
            case 'RETURN':
                return $this->parseReturnStatement();
            case 'SWITCH':
                return $this->parseSwitchStatement();
            case 'BREAK':
                return $this->parseBreakStatement();
            case 'CONTINUE':
                return $this->parseContinueStatement();
            case 'DELIMITER':
                if ($la->value === '{') {
                    return $this->parseBlock();
                }
                if ($la->value === ';') {
                    $this->match('DELIMITER');
                    return new StatementNode();
                }
                break;
        }
        throw new \RuntimeException("Unexpected token in Statement: {$la->type}");
    }

    /**
     * Faz o parsing de uma declaração de variável.
     *
     * Estrutura: Type Identifier [ '=' Expression ] ';'
     *
     * - Verifica se o token atual é um tipo válido (int, float, char, void).
     * - Lê o identificador.
     * - Opcionalmente, consome '=' e parseia uma expressão para inicialização.
     * - Exige ponto-e-vírgula ao final.
     *
     * @return DeclarationNode Nó de declaração contendo tipo, nome e inicializador (ou null).
     * @throws \RuntimeException Em caso de tipo inválido ou ponto-e-vírgula ausente.
     */
    private function parseDeclaration(): DeclarationNode
    {
        $typeToken = $this->lookahead();
        if (!in_array($typeToken->type, ['INT', 'FLOAT', 'CHAR', 'VOID'], true)) {
            throw new \RuntimeException(
                "Syntax error: esperava tipo (int|float|char|void), encontrou {$typeToken->type}"
            );
        }
        $this->pos++;
        $type = strtolower($typeToken->type);

        $idToken = $this->match('IDENTIFIER');
        $name    = $idToken->value;

        $initializer = null;
        if ($this->lookahead()->type === 'OPERATOR' && $this->lookahead()->value === '=') {
            $this->match('OPERATOR');
            $initializer = $this->parseExpression();
        }

        $semi = $this->match('DELIMITER');
        if ($semi->value !== ';') {
            throw new \RuntimeException(
                "Syntax error: esperava ';', encontrou '{$semi->value}'"
            );
        }

        return new DeclarationNode($type, $name, $initializer);
    }

    /**
     * Faz o parsing de uma atribuição de variável.
     *
     * Estrutura: Identifier '=' Expression ';'
     *
     * - Consome o identificador.
     * - Exige operador '='.
     * - Parseia a expressão do lado direito.
     * - Exige ponto-e-vírgula ao final.
     *
     * @return AssignmentNode Nó de atribuição contendo nome e expressão.
     * @throws \RuntimeException Em caso de operador ou ponto-e-vírgula incorretos.
     */
    private function parseAssignment(): AssignmentNode
    {
        $idToken = $this->match('IDENTIFIER');
        $name    = $idToken->value;

        $opToken = $this->match('OPERATOR');
        if ($opToken->value !== '=') {
            throw new \RuntimeException(
                "Syntax error: esperava '=', encontrou '{$opToken->value}'"
            );
        }

        $expr = $this->parseExpression();

        $semi = $this->match('DELIMITER');
        if ($semi->value !== ';') {
            throw new \RuntimeException(
                "Syntax error: esperava ';', encontrou '{$semi->value}'"
            );
        }

        return new AssignmentNode($name, $expr);
    }

    /**
     * Faz o parsing de um comando if/else.
     *
     * Estrutura:
     *   if ( Expressão ) Statement [ else Statement ]
     *
     * - Consome a palavra-chave 'if'.
     * - Parseia a expressão entre parênteses usando parseParenExpression().
     * - Parseia o statement do bloco 'then'.
     * - Opcionalmente, consome 'else' e parseia o statement do bloco 'else'.
     *
     * @return IfNode Nó representando o comando if (com eventual else).
     * @throws \RuntimeException Em caso de erro de sintaxe (parênteses ou statement ausente).
     */
    private function parseIfStatement(): IfNode
    {
        $this->match('IF');
        $cond = $this->parseParenExpression();
        $then = $this->parseStatement();

        $elseBranch = null;
        if ($this->lookahead()->type === 'ELSE') {
            $this->match('ELSE');
            $elseBranch = $this->parseStatement();
        }

        return new IfNode($cond, $then, $elseBranch);
    }

    /**
     * Faz o parsing de atribuição sem consumir o ponto-e-vírgula.
     *
     * Útil para o init de um for, onde o ';' é consumido externamente.
     * Estrutura:
     *   Identifier = Expression
     *
     * - Consome o identificador.
     * - Exige o operador '='.
     * - Parseia a expressão à direita.
     *
     * @return AssignmentNode Nó de atribuição contendo nome da variável e expressão.
     * @throws \RuntimeException Se o operador não for '=' ou expressão inválida.
     */
    private function parseAssignmentNoSemi(): AssignmentNode
    {
        $idToken = $this->match('IDENTIFIER');
        $name    = $idToken->value;

        $opToken = $this->match('OPERATOR');
        if ($opToken->value !== '=') {
            throw new \RuntimeException(
                "Syntax error: esperava '=', encontrou '{$opToken->value}'"
            );
        }

        $expr = $this->parseExpression();
        return new AssignmentNode($name, $expr);
    }

    /**
     * Faz o parsing de um laço for.
     *
     * Estrutura:
     *   for ( Init? ; Condition? ; Post? ) Statement
     *
     * - Consome 'for' e o parêntese de abertura.
     * - Parseia Init (declaração ou atribuição sem ';'), ou consome ';' vazio.
     * - Parseia Condition como expressão opcional, seguido de ';'.
     * - Parseia Post como expressão opcional, embrulhada em ExpressionStatementNode.
     * - Exige fechamento de parênteses e parseia o statement de corpo.
     *
     * @return ForNode Nó representando o laço for completo.
     * @throws \RuntimeException Em caso de erro de sintaxe em qualquer parte do cabeçalho do for.
     */
    private function parseForStatement(): ForNode
    {
        $this->match('FOR');

        $lpar = $this->match('DELIMITER');
        if ($lpar->value !== '(') {
            throw new \RuntimeException("Syntax error: esperava '(', encontrou '{$lpar->value}'");
        }

        $init = null;
        if (in_array($this->lookahead()->type, ['INT', 'FLOAT', 'CHAR', 'VOID'], true)) {
            $init = $this->parseDeclaration();
        } elseif ($this->lookahead()->type === 'IDENTIFIER') {
            $init = $this->parseAssignmentNoSemi();
            $semi = $this->match('DELIMITER');
            if ($semi->value !== ';') {
                throw new \RuntimeException("Syntax error: esperava ';' após init, encontrou '{$semi->value}'");
            }
        } else {
            $semi = $this->match('DELIMITER');
            if ($semi->value !== ';') {
                throw new \RuntimeException("Syntax error: esperava ';' após init vazio, encontrou '{$semi->value}'");
            }
        }

        $cond = null;
        if (!($this->lookahead()->type === 'DELIMITER' && $this->lookahead()->value === ';')) {
            $cond = $this->parseExpression();
        }
        $semi = $this->match('DELIMITER');
        if ($semi->value !== ';') {
            throw new \RuntimeException("Syntax error: esperava ';' após condição, encontrou '{$semi->value}'");
        }

        $post = null;
        if (!($this->lookahead()->type === 'DELIMITER' && $this->lookahead()->value === ')')) {
            $expr = $this->parseExpression();
            $post = new ExpressionStatementNode($expr);
        }

        $rpar = $this->match('DELIMITER');
        if ($rpar->value !== ')') {
            throw new \RuntimeException("Syntax error: esperava ')', encontrou '{$rpar->value}'");
        }

        $body = $this->parseStatement();
        return new ForNode($init, $cond, $post, $body);
    }

    /**
     * Faz o parsing de um bloco de statements delimitado por chaves.
     *
     * Estrutura:
     *   '{' Statement* '}'
     *
     * - Consome a chave de abertura '{'.
     * - Loopa parseando statements até encontrar '}'.
     * - Exige a chave de fechamento '}'.
     *
     * @return BlockNode Nó representando o bloco de statements.
     * @throws \RuntimeException Se faltar '{' ou '}' conforme esperado.
     */
    private function parseBlock(): BlockNode
    {
        $lbrace = $this->match('DELIMITER');
        if ($lbrace->value !== '{') {
            throw new \RuntimeException(
                "Syntax error: esperava '{', encontrou '{$lbrace->value}'"
            );
        }

        $stmts = [];
        while (!($this->lookahead()->type === 'DELIMITER' && $this->lookahead()->value === '}')) {
            $stmts[] = $this->parseStatement();
        }

        $rbrace = $this->match('DELIMITER');
        if ($rbrace->value !== '}') {
            throw new \RuntimeException(
                "Syntax error: esperava '}', encontrou '{$rbrace->value}'"
            );
        }

        return new BlockNode($stmts);
    }

    /**
     * Entry point para parsing de expressões.
     *
     * Retorna o nó de expressão mais alto, iniciando pelo nível de OR lógico.
     *
     * @return ExpressionNode Nó raiz da expressão.
     */
    private function parseExpression(): ExpressionNode
    {
        return $this->parseLogicalOr();
    }

    /**
     * Faz o parsing de expressões com operador lógico OR.
     *
     * Gramática:
     *   LogicalOr ::= LogicalAnd ( "||" LogicalAnd )*
     *
     * - Parseia o primeiro LogicalAnd.
     * - Enquanto encontrar '||', consome e parseia novo LogicalAnd,
     *   construindo BinaryOpNode.
     *
     * @return ExpressionNode Nó de expressão OR encadeada.
     */
    private function parseLogicalOr(): ExpressionNode
    {
        $node = $this->parseLogicalAnd();
        while (
            $this->lookahead()->type === 'OPERATOR'
            && $this->lookahead()->value === '||'
        ) {
            $op    = $this->match('OPERATOR')->value;
            $right = $this->parseLogicalAnd();
            $node  = new BinaryOpNode($op, $node, $right);
        }
        return $node;
    }

    /**
     * Faz o parsing de expressões com operador lógico AND.
     *
     * Gramática:
     *   LogicalAnd ::= Equality ( "&&" Equality )*
     *
     * - Parseia o primeiro Equality.
     * - Enquanto encontrar '&&', consome e parseia novo Equality,
     *   construindo BinaryOpNode.
     *
     * @return ExpressionNode Nó de expressão AND encadeada.
     */
    private function parseLogicalAnd(): ExpressionNode
    {
        $node = $this->parseEquality();
        while (
            $this->lookahead()->type === 'OPERATOR'
            && $this->lookahead()->value === '&&'
        ) {
            $op    = $this->match('OPERATOR')->value;
            $right = $this->parseEquality();
            $node  = new BinaryOpNode($op, $node, $right);
        }
        return $node;
    }

    /**
     * Faz o parsing de expressões de igualdade.
     *
     * Gramática:
     *   Equality ::= Relational ( ("==" | "!=") Relational )*
     *
     * - Parseia o primeiro Relational.
     * - Enquanto encontrar '==' ou '!=', consome e parseia novo Relational,
     *   construindo BinaryOpNode.
     *
     * @return ExpressionNode Nó de expressão de igualdade.
     */
    private function parseEquality(): ExpressionNode
    {
        $node = $this->parseRelational();
        while (
            $this->lookahead()->type === 'OPERATOR'
            && in_array($this->lookahead()->value, ['==', '!='], true)
        ) {
            $op    = $this->match('OPERATOR')->value;
            $right = $this->parseRelational();
            $node  = new BinaryOpNode($op, $node, $right);
        }
        return $node;
    }

    /**
     * Faz o parsing de expressões relacionais.
     *
     * Gramática:
     *   Relational ::= Additive ( ("<" | "<=" | ">" | ">=") Additive )*
     *
     * - Parseia o primeiro Additive.
     * - Enquanto encontrar operadores relacionais, consome e parseia novo Additive,
     *   construindo BinaryOpNode.
     *
     * @return ExpressionNode Nó de expressão relacional.
     */
    private function parseRelational(): ExpressionNode
    {
        $node = $this->parseAdditive();
        while (
            $this->lookahead()->type === 'OPERATOR'
            && in_array($this->lookahead()->value, ['<', '<=', '>', '>='], true)
        ) {
            $op    = $this->match('OPERATOR')->value;
            $right = $this->parseAdditive();
            $node  = new BinaryOpNode($op, $node, $right);
        }
        return $node;
    }

    /**
     * Faz o parsing de expressões aditivas.
     *
     * Gramática:
     *   Additive ::= Term ( ("+" | "-") Term )*
     *
     * - Parseia o primeiro Term.
     * - Enquanto encontrar '+' ou '-', consome e parseia novo Term,
     *   construindo BinaryOpNode.
     *
     * @return ExpressionNode Nó de expressão aditiva.
     */
    private function parseAdditive(): ExpressionNode
    {
        $node = $this->parseTerm();
        while (
            $this->lookahead()->type === 'OPERATOR'
            && in_array($this->lookahead()->value, ['+', '-'], true)
        ) {
            $op    = $this->match('OPERATOR')->value;
            $right = $this->parseTerm();
            $node  = new BinaryOpNode($op, $node, $right);
        }
        return $node;
    }

    /**
     * Faz o parsing de termos (multiplicação/divisão).
     *
     * Gramática:
     *   Term ::= Factor Term'
     *
     * - Parseia o primeiro Factor.
     * - Delegação para parseTermPrime para lidar com múltiplos '*' ou '/'.
     *
     * @return ExpressionNode Nó de expressão de termo.
     */
    private function parseTerm(): ExpressionNode
    {
        $node = $this->parseFactor();
        return $this->parseTermPrime($node);
    }

    /**
     * Continua o parsing de operações de multiplicação e divisão em sequência.
     *
     * Gramática:
     *   Term' ::= ("*" | "/") Factor Term' | ε
     *
     * - Se encontrar '*' ou '/', consome, parseia Factor e chama recursivamente
     *   para lidar com mais operadores.
     *
     * @param ExpressionNode $left Nó da parte esquerda já parseada.
     * @return ExpressionNode Nó de expressão completo após multiplicações/divisões.
     */
    private function parseTermPrime(ExpressionNode $left): ExpressionNode
    {
        $la = $this->lookahead();
        if ($la->type === 'OPERATOR' && in_array($la->value, ['*', '/'], true)) {
            $op    = $this->match('OPERATOR')->value;
            $right = $this->parseFactor();
            $bin   = new BinaryOpNode($op, $left, $right);
            return $this->parseTermPrime($bin);
        }
        return $left;
    }

    /**
     * Faz o parsing de fatores (números, literais, identificadores, unários, chamadas de função ou agrupamento).
     *
     * Gramática:
     *   Factor ::= ('!' | '-' | '+') Factor
     *            | NUMBER
     *            | STRING_LITERAL
     *            | IDENTIFIER [ '(' ArgumentList? ')' ]
     *            | '(' Expression ')'
     *
     * - Lida com operador unário recursivamente.
     * - Reconhece literais numéricos e de string.
     * - Identifica chamadas de função ou variáveis.
     * - Parseia expressões agrupadas por parênteses.
     *
     * @return ExpressionNode Nó de fator correspondente ao token atual.
     * @throws \RuntimeException Em caso de token inválido para fator.
     */
    private function parseFactor(): ExpressionNode
    {
        $la = $this->lookahead();
        if ($la->type === 'OPERATOR' && in_array($la->value, ['!', '-', '+'], true)) {
            $op   = $this->match('OPERATOR')->value;
            $expr = $this->parseFactor();
            return new UnaryOpNode($op, $expr);
        }
        if ($la->type === 'NUMBER') {
            $token = $this->match('NUMBER');
            return new NumberNode($token->value);
        }
        if ($la->type === 'STRING_LITERAL') {
            $token = $this->match('STRING_LITERAL');
            return new StringLiteralNode($token->value);
        }
        if ($la->type === 'IDENTIFIER') {
            $name = $this->match('IDENTIFIER')->value;
            if ($this->lookahead()->type === 'DELIMITER' && $this->lookahead()->value === '(') {
                return $this->parseFunctionCall($name);
            }
            return new IdentifierNode($name);
        }
        if ($la->type === 'DELIMITER' && $la->value === '(') {
            return $this->parseParenExpression();
        }
        throw new \RuntimeException(
            "Syntax error: esperava um fator (unário, número, string, identificador, chamada de função ou parêntese), encontrou {$la->type}"
        );
    }

    /**
     * Faz o parsing de uma chamada de função.
     *
     * Estrutura:
     *   IDENTIFIER '(' [ Expression (',' Expression)* ] ')'
     *
     * - Consome o parêntese de abertura.
     * - Parseia zero ou mais argumentos separados por vírgula.
     * - Consome o parêntese de fechamento.
     *
     * @param string $name Nome da função a ser chamada.
     * @return FunctionCallNode Nó representando a chamada de função.
     * @throws \RuntimeException Se os parênteses não forem encontrados corretamente.
     */
    private function parseFunctionCall(string $name): FunctionCallNode
    {
        $lpar = $this->match('DELIMITER');
        if ($lpar->value !== '(') {
            throw new \RuntimeException("esperava '(', encontrou '{$lpar->value}'");
        }
        $args = [];
        if (!($this->lookahead()->type === 'DELIMITER' && $this->lookahead()->value === ')')) {
            do {
                $args[] = $this->parseExpression();
                if ($this->lookahead()->type === 'DELIMITER' && $this->lookahead()->value === ',') {
                    $this->match('DELIMITER');
                } else {
                    break;
                }
            } while (true);
        }
        $rpar = $this->match('DELIMITER');
        if ($rpar->value !== ')') {
            throw new \RuntimeException("esperava ')', encontrou '{$rpar->value}'");
        }
        return new FunctionCallNode($name, $args);
    }

    /**
     * Faz o parsing de um laço while.
     *
     * Estrutura:
     *   while ( Expressão ) Statement
     *
     * - Consome a palavra-chave 'while'.
     * - Parseia a expressão condicional entre parênteses.
     * - Parseia o corpo do laço.
     *
     * @return WhileNode Nó representando o laço while.
     * @throws \RuntimeException Se houver erro de sintaxe nos parênteses.
     */
    private function parseWhileStatement(): WhileNode
    {
        $this->match('WHILE');
        $cond = $this->parseParenExpression();
        $body = $this->parseStatement();
        return new WhileNode($cond, $body);
    }

    /**
     * Faz o parsing de um comando return.
     *
     * Estrutura:
     *   return [ Expressão ] ;
     *
     * - Consome a palavra-chave 'return'.
     * - Opcionalmente, parseia uma expressão de retorno.
     * - Exige ponto-e-vírgula ao final.
     *
     * @return ReturnNode Nó representando o comando return.
     * @throws \RuntimeException Se faltar o ponto-e-vírgula.
     */
    private function parseReturnStatement(): ReturnNode
    {
        $this->match('RETURN');
        $expr = null;
        if (!($this->lookahead()->type === 'DELIMITER' && $this->lookahead()->value === ';')) {
            $expr = $this->parseExpression();
        }
        $semi = $this->match('DELIMITER');
        if ($semi->value !== ';') {
            throw new \RuntimeException("Syntax error: esperava ';' após return");
        }
        return new ReturnNode($expr);
    }

    /**
     * Faz o parsing da definição de uma função.
     *
     * Estrutura:
     *   Tipo IDENTIFIER '(' [ ParamList ] ')' Block
     *
     * - Consome o tipo de retorno e o nome da função.
     * - Parseia a lista de parâmetros entre parênteses.
     * - Parseia o corpo da função como um bloco.
     *
     * @return FunctionNode Nó representando a definição da função.
     * @throws \RuntimeException Se houver erro de sintaxe nos parênteses ou bloco.
     */
    private function parseFunctionDefinition(): FunctionNode
    {
        $typeToken  = $this->match($this->lookahead()->type);
        $returnType = strtolower($typeToken->type);
        $nameToken = $this->match('IDENTIFIER');
        $name      = $nameToken->value;
        $lpar = $this->match('DELIMITER');
        if ($lpar->value !== '(') {
            throw new \RuntimeException("esperava '(', encontrou '{$lpar->value}'");
        }
        $parameters = [];
        if (!($this->lookahead()->type === 'DELIMITER' && $this->lookahead()->value === ')')) {
            $parameters = $this->parseParameterList();
        }
        $rpar = $this->match('DELIMITER');
        if ($rpar->value !== ')') {
            throw new \RuntimeException("esperava ')', encontrou '{$rpar->value}'");
        }
        $body = $this->parseBlock();
        return new FunctionNode($returnType, $name, $parameters, $body);
    }

    /**
     * Faz o parsing da lista de parâmetros de uma função.
     *
     * Estrutura:
     *   (Tipo IDENTIFIER) (',' Tipo IDENTIFIER)*
     *
     * - Para cada parâmetro, consome o tipo e o identificador.
     * - Se houver vírgula, continua parseando o próximo parâmetro.
     * - Para ao encontrar o fechamento do parêntese.
     *
     * @return ParameterNode[] Lista de parâmetros parseados.
     * @throws \RuntimeException Se o tipo do parâmetro for inválido.
     */
    private function parseParameterList(): array
    {
        $params = [];
        do {
            $typeToken = $this->lookahead();
            if (!in_array($typeToken->type, ['INT', 'FLOAT', 'CHAR', 'VOID'], true)) {
                throw new \RuntimeException(
                    "Syntax error: esperava tipo de parâmetro, encontrou {$typeToken->type}"
                );
            }
            $this->pos++;
            $paramType = strtolower($typeToken->type);
            $nameToken = $this->match('IDENTIFIER');
            $paramName = $nameToken->value;
            $params[] = new ParameterNode($paramType, $paramName);
            if ($this->lookahead()->type === 'DELIMITER' && $this->lookahead()->value === ',') {
                $this->match('DELIMITER');
            } else {
                break;
            }
        } while (true);
        return $params;
    }

    /**
     * Faz o parsing de um comando break.
     *
     * Estrutura:
     *   break ;
     *
     * - Consome a palavra-chave 'break'.
     * - Exige ponto-e-vírgula ao final.
     *
     * @return BreakNode Nó representando o comando break.
     * @throws \RuntimeException Se faltar o ponto-e-vírgula.
     */
    private function parseBreakStatement(): BreakNode
    {
        $this->match('BREAK');
        $semi = $this->match('DELIMITER');
        if ($semi->value !== ';') {
            throw new \RuntimeException("Syntax error: esperava ';' após break");
        }
        return new BreakNode();
    }

    /**
     * Faz o parsing de um comando continue.
     *
     * Estrutura:
     *   continue ;
     *
     * - Consome a palavra-chave 'continue'.
     * - Exige ponto-e-vírgula ao final.
     *
     * @return ContinueNode Nó representando o comando continue.
     * @throws \RuntimeException Se faltar o ponto-e-vírgula.
     */
    private function parseContinueStatement(): ContinueNode
    {
        $this->match('CONTINUE');
        $semi = $this->match('DELIMITER');
        if ($semi->value !== ';') {
            throw new \RuntimeException("Syntax error: esperava ';' após continue");
        }
        return new ContinueNode();
    }

    /**
     * Faz o parsing de um comando switch.
     *
     * Estrutura:
     *   switch ( Expressão ) { CaseClause* [ DefaultClause ] }
     *
     * - Consome a palavra-chave 'switch' e a expressão entre parênteses.
     * - Parseia os casos (case) e o bloco default, se houver.
     * - Exige chaves de abertura e fechamento.
     *
     * @return SwitchNode Nó representando o comando switch.
     * @throws \RuntimeException Se houver erro de sintaxe nas chaves ou nos casos.
     */
    private function parseSwitchStatement(): SwitchNode
    {
        $this->match('SWITCH');
        $expr = $this->parseParenExpression();
        $lbrace = $this->match('DELIMITER');
        if ($lbrace->value !== '{') {
            throw new \RuntimeException("Syntax error: esperava '{', encontrou '{$lbrace->value}'");
        }
        $cases = [];
        while ($this->lookahead()->type === 'CASE') {
            $cases[] = $this->parseCaseClause();
        }
        $defaultStmts = [];
        if ($this->lookahead()->type === 'DEFAULT') {
            $this->match('DEFAULT');
            $colon = $this->match('DELIMITER');
            if ($colon->value !== ':') {
                throw new \RuntimeException("Syntax error: esperava ':', encontrou '{$colon->value}'");
            }
            while (
                !($this->lookahead()->type === 'DELIMITER' && $this->lookahead()->value === '}')
                && $this->lookahead()->type !== 'CASE'
            ) {
                $defaultStmts[] = $this->parseStatement();
            }
        }
        $rbrace = $this->match('DELIMITER');
        if ($rbrace->value !== '}') {
            throw new \RuntimeException("Syntax error: esperava '}', encontrou '{$rbrace->value}'");
        }
        return new SwitchNode($expr, $cases, $defaultStmts);
    }

    /**
     * Faz o parsing de um case dentro de um switch.
     *
     * Estrutura:
     *   case Expressão : Statement*
     *
     * - Consome a palavra-chave 'case', a expressão e os dois pontos.
     * - Parseia os statements do case até encontrar outro case, default ou fechamento do bloco.
     *
     * @return CaseNode Nó representando o case.
     * @throws \RuntimeException Se faltar os dois pontos após o valor do case.
     */
    private function parseCaseClause(): CaseNode
    {
        $this->match('CASE');
        $value  = $this->parseExpression();
        $colon = $this->match('DELIMITER');
        if ($colon->value !== ':') {
            throw new \RuntimeException("esperava ':', encontrou '{$colon->value}'");
        }
        $stmts = [];
        while (
            !($this->lookahead()->type === 'DELIMITER' && $this->lookahead()->value === '}')
            && $this->lookahead()->type !== 'CASE'
            && $this->lookahead()->type !== 'DEFAULT'
        ) {
            $stmts[] = $this->parseStatement();
        }
        return new CaseNode($value, $stmts);
    }

    /**
     * Faz o parsing de uma expressão entre parênteses.
     *
     * Estrutura:
     *   ( Expressão )
     *
     * - Consome o parêntese de abertura.
     * - Parseia a expressão.
     * - Consome o parêntese de fechamento.
     *
     * @return ExpressionNode Nó representando a expressão agrupada.
     * @throws \RuntimeException Se faltar algum parêntese.
     */
    private function parseParenExpression(): ExpressionNode
    {
        $this->match('DELIMITER', '(');
        $expr = $this->parseExpression();
        $this->match('DELIMITER', ')');
        return $expr;
    }
}
