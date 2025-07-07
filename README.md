# CMM – Compilador da Linguagem C em PHP

Este é um projeto acadêmico em andamento com o objetivo de construir, passo a passo, um **compilador completo da linguagem C**, escrito em **PHP** e executado via **PHP-CLI**.

Atualmente, esta versão implementa:

- **Análise léxica (lexer)**, reconhecendo os principais tokens da linguagem C.  
- **Análise sintática (parser)** recursivo-descendente, com suporte a expressões completas, controle de fluxo e chamadas de função.  
- **Análise semântica**, incluindo verificação de tipos, escopo de variáveis, declaração e uso correto de identificadores, além de detecção de erros semânticos comuns.  
- **Gerador de código**, que transforma a AST em código intermediário ou código alvo, permitindo a execução ou análise posterior do programa.  
- **Otimizações**, como eliminação de código morto, simplificação de expressões e otimização de loops, visando melhorar a eficiência do código gerado.  
- **Tratamento de Erros Aprimorado**  
  - Mensagens de erro léxico, sintático e semântico detalhadas, indicando linha e coluna.  
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
    * Palavras‐chave: `int`, `float`, `char`, `void`, `if`, `else`, `return`, `while`, `for`, `break`, `continue`, `switch`, `case`, `default`
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

* **Semantic Analyzer**
  * Verificação de escopo e declarações:
    * Variáveis não declaradas
    * Redeclaração de variáveis
    * Uso de `break`/`continue` fora de loops
    * `return` fora de funções ou tipos incompatíveis de retorno
  * Checagem de tipos em expressões aritméticas, relacionais e lógicas
  * Validação de compatibilidade em atribuições e parâmetros de função
  * Emissão de erros semânticos com indicação de linha/coluna
  * Suporte a testes automáticos (PHPUnit) com arquivos de casos válidos e inválidos

* **Code Generator**
  * Constant folding para operações entre literais (`+, -, *, /, %`)
  * Strength reduction de multiplicações por potências de dois (transforma em `shl`)
  * Geração de código x86-64 para operadores:
    * Aritméticos: `+`, `-`, `*`, `/`, `%`
    * Relacionais: `<`, `<=`, `>`, `>=`, `==`, `!=` (produz 0 ou 1 em `EAX`)
    * Lógicos: `&&`, `||` (via comparações e operações em `AL`)
  * Empilhamento (`push rax`/`pop rbx`) e uso de registradores (`eax`, `ebx`, `ecx`, `edx`)
  * Tratamento de divisão inteira com sinal (`cdq` + `idiv`)
  * Emissão de `movzx` para estender resultados booleanos de 8-bit para 32/64-bit

---

## 🧪 Testes Automatizados

Execute a suíte de testes com PHPUnit:

```bash
vendor/bin/phpunit
````

Para testar apenas uma parte específica:

* **Parser**

  ```bash
  vendor/bin/phpunit --filter ParserIntegrationTest

* **Analisador Semântico**

  ```bash
  vendor/bin/phpunit --filter SemanticAnalyzerTest

- Os testes estão em `tests/`, cobrindo casos de operadores lógicos (`and.c`, `or.c`, `not.c`) e arquivos com erros de sintaxe/lexema.
- Garante que novas melhorias não quebrem funcionalidades existentes.


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

2. Instale o autoload do Composer:

   ```bash
   composer dump-autoload

3. Adicione ou edite o arquivo `example.c` com um código C simples.

4. Execute:

   ```bash
   php run.php basic_example.c

  Isso irá:

   * Executar o programa normalmente.
   * Gerar um arquivo `basic_example.asm` contendo o código Assembly.

   Você encontrará o `.asm` em:

  ```bash 
  output/assembly/basic_example.asm
  ```
---

## 🤝 Contribuição

Este projeto é acadêmico e está sendo desenvolvido aos poucos, com foco em aprendizado e aplicação prática de teoria de compiladores. Sugestões e melhorias são bem-vindas!

---

## 📄 Licença

Este projeto está sob a licença MIT.

