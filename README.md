# CMM – Compilador da Linguagem C em PHP

Este é um projeto acadêmico em andamento com o objetivo de construir, passo a passo, um **compilador completo da linguagem C**, escrito em **PHP** e executado via **PHP-CLI**.

Atualmente, esta versão implementa:

- **Análise léxica (lexer)**, reconhecendo os principais tokens da linguagem C.  
- **Análise sintática (parser)** recursivo-descendente, com suporte a expressões completas, controle de fluxo e chamadas de função.  
- **Tratamento de Erros Aprimorado**  
  - Mensagens de erro léxico e sintático detalhadas, indicando linha e coluna.  
  - Exemplos claros de saída de erro para facilitar a depuração.

---

## 📖 Gramática Atualizada
````text

Program
 └─ Statement*

Statement
 ├─ Declaration
 │    └─ Type Identifier [ '=' Expression ] ';'
 ├─ Assignment
 │    └─ Identifier '=' Expression ';'
 ├─ IfStatement
 │    └─ "if" "(" Expression ")" Statement [ "else" Statement ]
 ├─ WhileStatement
 │    └─ "while" "(" Expression ")" Statement
 ├─ ForStatement
 │    └─ "for" "(" Init? ";" Expression? ";" Expression? ")" Statement
 ├─ SwitchStatement
 │    └─ "switch" "(" Expression ")" "{" CaseClause* DefaultClause? "}"
 ├─ ExpressionStatement
 │    └─ Expression ";"
 ├─ Block
 │    └─ "{" Statement* "}"
 ├─ ReturnStatement
 │    └─ "return" [ Expression ] ";"
 ├─ BreakStatement
 │    └─ "break" ";"
 ├─ ContinueStatement
 │    └─ "continue" ";"
 └─ ";"   (empty statement)

Init
 ├─ Declaration
 └─ AssignmentNoSemi

Expression
 └─ LogicalOr

LogicalOr
 └─ LogicalAnd ( "||" LogicalAnd )*

LogicalAnd
 └─ Equality ( "&&" Equality )*

Equality
 └─ Relational ( ("==" | "!=") Relational )*

Relational
 └─ Additive ( ("<" | "<=" | ">" | ">=") Additive )*

Additive
 └─ Term ( ("+" | "-") Term )*

Term
 └─ Factor ( ("*" | "/") Factor )*

Factor
 ├─ UnaryOp Factor          # '!' | '-' | '+'
 ├─ NUMBER
 ├─ STRING_LITERAL
 ├─ IDENTIFIER [ '(' ArgumentList? ')' ]
 └─ "(" Expression ")"

ArgumentList
 └─ Expression ( "," Expression )*
````

---

## 📆 Funcionalidades atuais

* **Lexer**

  * Comentários: `//`, `/* ... */`
  * Tokens:

    * Palavras-chave: `int`, `float`, `char`, `void`, `if`, `else`, `return`, `while`, `for`, `break`, `continue`, `switch`, `case`, `default`
    * Identificadores (incluindo `.` em nomes de arquivos)
    * Literais: números (inteiros e floats), strings (`"texto\n"`)
    * Operadores: `+`, `-`, `*`, `/`, `=`, `==`, `!=`, `<`, `>`, `<=`, `>=`, `&&`, `||`
    * Delimitadores: `()`, `{}`, `[]`, `;`, `,`, `:`

* **Parser**

  * AST com nós dedicados:

    * `ProgramNode`, `BlockNode`, `DeclarationNode`, `AssignmentNode`, `ExpressionStatementNode`
    * Expressões: `BinaryOpNode`, `UnaryOpNode`, `NumberNode`, `StringLiteralNode`, `IdentifierNode`, `FunctionCallNode`
    * Controle de fluxo: `IfNode`, `WhileNode`, `ForNode`, `SwitchNode`, `CaseNode`, `BreakNode`, `ContinueNode`, `ReturnNode`
    * Funções: `FunctionNode`, `ParameterNode`
  * Recursive-descent por precedência de operadores
  * Extração de expressões entre parênteses via `parseParenExpression()`
  * Suporte a chamadas de função e expression-statements

---

## 🧪 Testes Automatizados

Execute a suíte de testes com PHPUnit:

```bash
vendor/bin/phpunit
```

* Os testes estão em `tests/`, cobrindo casos de operadores lógicos (`and.c`, `or.c`, `not.c`) e arquivos com erros de sintaxe/lexema.
* Garante que novas melhorias não quebrem funcionalidades existentes.

---

## ✅ Pré-requisitos

Antes de executar o compilador, verifique se você possui:

* **PHP 8.0 ou superior**

  ```bash
  php -v
  ```
* **Composer**

  ```bash
  composer --version
  ```

Caso precise instalar, consulte:

* [PHP Downloads](https://www.php.net/downloads)
* [Composer Installation](https://getcomposer.org/download/)

---

## 🚀 Como rodar

1. Clone o repositório:

   ```bash
   git clone https://github.com/seu-usuario/cmm.git
   cd cmm
   ```
2. Instale o autoload do Composer:

   ```bash
   composer dump-autoload
   ```
3. Adicione ou edite o arquivo `example.c` com um código C simples.
4. Execute:

   ```bash
   php run.php example.c
   ```

---

## 🤝 Contribuição

Este projeto é acadêmico e está sendo desenvolvido aos poucos, com foco em aprendizado e aplicação prática de teoria de compiladores. Sugestões e melhorias são bem-vindas!

---

## 📄 Licença

Este projeto está sob a licença MIT.

```
```
