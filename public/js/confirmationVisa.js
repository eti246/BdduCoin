// Auteur : Etienne Desrochers
// Date : 2020-05-06
// But : Valide le numero de carte selon le patern

function confirmeNumeroVisa(Numero)
{
    // 4 [13 chiffre] [3 chiffre optionnel]
    const regex = RegExp("4[0-9]{12}(?:[0-9]{3})?");
    return regex.test(Numero);
}