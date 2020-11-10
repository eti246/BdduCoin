// Auteur : Etienne Desrochers
// Date : 2020-05-06
// But : Valide le numero de carte selon le patern

function confirmeNumeroMaster(Numero)
{
    // 5 [1 ou 2 ou 3 ou 4 ou 5] [14 Chiffre]
    const regex = RegExp("5[1-5][0-9]{14}");
    return regex.test(Numero);
}