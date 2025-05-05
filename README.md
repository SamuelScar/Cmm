# CMM – Compilador da Linguagem C em PHP

Este é um projeto acadêmico em andamento com o objetivo de construir, passo a passo, um **compilador completo da linguagem C**, escrito em **PHP** e executado via **PHP-CLI**.

Atualmente, esta versão implementa a **análise léxica (lexer)** com reconhecimento dos principais tokens da linguagem C.

---

## 📆 Funcionalidades atuais

- Leitura de código-fonte C a partir de um arquivo `.c`
- Identificação dos seguintes tipos de tokens:
  - Palavras-chave: `int`, `float`, `if`, `else`, `return`, `while`, `for`, `void`
  - Identificadores *(nomes definidos pelo programador, como variáveis, funções ou constantes; exemplos: `total`, `contador`, `main`)*
  - Números inteiros e reais *(valores numéricos como `10`, `0`, `-42`, `3.14`, `-0.5`, `2e10`)*
  - Operadores: `+`, `-`, `*`, `/`, `=`, `==`, `!=`, `<`, `>`, `<=`, `>=`, `&&`, `||`
  - Delimitadores: `() {} ; , []`
  - Ignora comentários (`//` e `/* */`) e espaços em branco
- Impressão da sequência de tokens no terminal

---

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
├── src/              # Classes do compilador
│   ├── Token.php
│   └── Lexer.php
├── exemple.c         # Arquivo de código C para testes
├── run.php           # Ponto de entrada via terminal
├── composer.json     # Configuração do autoload
└── vendor/           # Gerado pelo Composer
```

---


## 🤝 Contribuição

Este projeto é acadêmico e está sendo desenvolvido aos poucos, com foco em aprendizado e aplicação prática de teoria de compiladores. Sugestões e melhorias são bem-vindas!

---

## 📄 Licença

Este projeto está sob a licença MIT.

