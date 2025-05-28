#include <stdio.h>

int main() {
    switch (1) {
        case 1:
            break   // <-- aqui falta o ponto-e-vírgula
        default:
            ;
    }
    return 0;
}
