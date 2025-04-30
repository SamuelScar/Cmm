# CMM – Compilador da Linguagem C em PHP

Este é um projeto acadêmico em andamento com o objetivo de construir, passo a passo, um **compilador completo da linguagem C**, escrito em **PHP** e executado via **PHP-CLI**.

Atualmente, esta versão implementa a **análise léxica (lexer)** com reconhecimento dos principais tokens da linguagem C.

---

## 📆 Funcionalidades atuais

- Leitura de código-fonte C a partir de um arquivo `.c`
- Identificação dos seguintes tipos de tokens:
  - Palavras-chave: `int`, `float`, `if`, `else`, `return`, `while`, `for`, `void`
  - Identificadores
  - Números inteiros e reais
  - Operadores: `+`, `-`, `*`, `/`, `=`, `==`, `!=`, `<`, `>`, `<=`, `>=`, `&&`, `||`
  - Delimitadores: `() {} ; , []`
  - Ignora comentários (`//` e `/* */`) e espaços em branco
- Impressão da sequência de tokens no terminal

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
├── src/              # Classes do compilador
│   ├── Token.php
│   └── Lexer.php
├── exemple.c         # Arquivo de código C para testes
├── run.php           # Ponto de entrada via terminal
├── composer.json     # Configuração do autoload
└── vendor/           # Gerado pelo Composer
```

---

## 🛠️ Em breve...

Este projeto será expandido com:

- Rastreamento de linha e coluna dos tokens
- Saída opcional em JSON
- Construção de tabela de símbolos
- Análise sintática (parser)
- Análise semântica
- Geração de código intermediário ou assembly
- Simulador de execução

---

## 🤝 Contribuição

Este projeto é acadêmico e está sendo desenvolvido aos poucos, com foco em aprendizado e aplicação prática de teoria de compiladores. Sugestões e melhorias são bem-vindas!

---

## 📄 Licença

Este projeto está sob a licença MIT.

