/*
 * basic_example.c
 *
 * Arquivo de exemplo para demonstrar o funcionamento completo do compilador:
 * - Função de soma
 * - Controle de fluxo (for, while, if/else, switch)
 *
 * Serve como caso de teste para as fases de análise léxica,
 * análise sintática, análise semântica e geração de código assembly.
 */

int soma(int x, int y) {
    return x + y;
}

int main() {
    int a = 0;
    int b = 5;
    int c;

    for (int i = 0; i < 10; i = i + 1) {
        if (i % 2 == 0) {
            continue;
        }

        if (i == 7) {
            break;
        }

        a = a + i;
    }

    while (a < 30) {
        a = a + 1;
    }

    if (a > b && b < 10) {
        c = soma(a, b);
    } else {
        c = a - b;
    }

    switch (c) {
        case 5:
            c = c + 10;
            break;
        case 10:
            c = c + 20;
            break;
        default:
            c = 0;
    }

    return c;
}
