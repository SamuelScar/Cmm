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

class Parser
{
    /** @var Token[] */
    private array $tokens;
    private int $pos = 0;

    public function __construct(array $tokens)
    {
        $this->tokens = $tokens;
    }

    private function lookahead(): Token
    {
        return $this->tokens[$this->pos] ?? new Token('EOF', '');
    }

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

    public function parseProgram(): ProgramNode
    {
        $statements = [];
        while ($this->lookahead()->type !== 'EOF') {
            $statements[] = $this->parseStatement();
        }
        return new ProgramNode($statements);
    }

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

    private function parseExpression(): ExpressionNode
    {
        return $this->parseLogicalOr();
    }

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

    private function parseTerm(): ExpressionNode
    {
        $node = $this->parseFactor();
        return $this->parseTermPrime($node);
    }

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

    private function parseWhileStatement(): WhileNode
    {
        $this->match('WHILE');
        $cond = $this->parseParenExpression();
        $body = $this->parseStatement();
        return new WhileNode($cond, $body);
    }

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

    private function parseBreakStatement(): BreakNode
    {
        $this->match('BREAK');
        $semi = $this->match('DELIMITER');
        if ($semi->value !== ';') {
            throw new \RuntimeException("Syntax error: esperava ';' após break");
        }
        return new BreakNode();
    }

    private function parseContinueStatement(): ContinueNode
    {
        $this->match('CONTINUE');
        $semi = $this->match('DELIMITER');
        if ($semi->value !== ';') {
            throw new \RuntimeException("Syntax error: esperava ';' após continue");
        }
        return new ContinueNode();
    }

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

    private function parseParenExpression(): ExpressionNode
    {
        $this->match('DELIMITER', '(');
        $expr = $this->parseExpression();
        $this->match('DELIMITER', ')');
        return $expr;
    }
}