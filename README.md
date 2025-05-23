# CMM – Compilador da Linguagem C em PHP

Este é um projeto acadêmico em andamento com o objetivo de construir, passo a passo, um **compilador completo da linguagem C**, escrito em **PHP** e executado via **PHP-CLI**.

Atualmente, esta versão implementa:

- Análise léxica (lexer), reconhecendo os principais tokens da linguagem C.  
- Análise sintática (parser) recursivo-descendente, com suporte a expressões completas, controle de fluxo e chamadas de função.

---

## 📖 Gramática Atualizada

```text
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
 └─ Expression ( "," Expression )*```

```

---

## 📆 Funcionalidades atuais

- **Lexer**  
  - Comentários: `//`, `/* ... */`  
  - Tokens:  
    - Palavras-chave: `int`, `float`, `char`, `void`, `if`, `else`, `return`, `while`, `for`, `break`, `continue`, `switch`, `case`, `default`  
    - Identificadores (incluindo `.` em nomes de arquivos)  
    - Literais: números (inteiros e floats), strings (`"texto\n"`)  
    - Operadores: `+`, `-`, `*`, `/`, `=`, `==`, `!=`, `<`, `>`, `<=`, `>=`, `&&`, `||`  
    - Delimitadores: `()`, `{}`, `[]`, `;`, `,`, `:`  

- **Parser**  
  - AST com nós dedicados:  
    - `ProgramNode`, `BlockNode`, `DeclarationNode`, `AssignmentNode`, `ExpressionStatementNode`  
    - Expressões: `BinaryOpNode`, `UnaryOpNode`, `NumberNode`, `StringLiteralNode`, `IdentifierNode`, `FunctionCallNode`  
    - Controle de fluxo: `IfNode`, `WhileNode`, `ForNode`, `SwitchNode`, `CaseNode`, `BreakNode`, `ContinueNode`, `ReturnNode`  
    - Funções: `FunctionNode`, `ParameterNode`  
  - Recursive-descent por precedência de operadores  
  - Retirada de duplicação de parênteses via `parseParenExpression()`  
  - Suporte a chamadas de função e expression-statements  

---

## ✅ Pré-requisitos

Antes de executar o compilador, verifique se você possui os seguintes itens instalados na sua máquina:

- **PHP 8.0 ou superior**  
  Recomendado para garantir compatibilidade com sintaxe moderna e desempenho adequado.  
  Verifique com:  
  ```bash
  php -v
  ```

- **Composer**  
  Utilizado para autoload das classes.  
  Verifique com:  
  ```bash
  composer --version
  ```

Caso não tenha o PHP ou o Composer instalados, consulte a documentação oficial:

- [PHP Downloads](https://www.php.net/downloads)
- [Composer Installation](https://getcomposer.org/download/)

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

3. Adicione ou edite o arquivo `exemple.c` com um código C simples.

4. Execute:
   ```bash
   php run.php exemple.c
   ```

---

## 📂 Estrutura do projeto

```
cmm/
├── src/
│   ├── Lexer.php
│   ├── Parser.php
│   ├── Token.php
│   └── Node/
│       ├── ProgramNode.php
│       ├── StatementNode.php
│       ├── DeclarationNode.php
│       ├── AssignmentNode.php
│       ├── ExpressionStatementNode.php
│       ├── NumberNode.php
│       ├── StringLiteralNode.php
│       ├── IdentifierNode.php
│       ├── BinaryOpNode.php
│       ├── UnaryOpNode.php
│       ├── FunctionCallNode.php
│       ├── IfNode.php
│       ├── WhileNode.php
│       ├── ForNode.php
│       ├── SwitchNode.php
│       ├── CaseNode.php
│       ├── ReturnNode.php
│       ├── BreakNode.php
│       ├── ContinueNode.php
│       ├── FunctionNode.php
│       └── ParameterNode.php
├── examples/
│   ├── not.c
│   ├── and.c
│   └── or.c
├── run.php
├── composer.json
└── vendor/
```

---

## 🤝 Contribuição

Este projeto é acadêmico e está sendo desenvolvido aos poucos, com foco em aprendizado e aplicação prática de teoria de compiladores. Sugestões e melhorias são bem-vindas!

---

## 📄 Licença

Este projeto está sob a licença MIT.
