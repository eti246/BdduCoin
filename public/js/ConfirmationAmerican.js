// Auteur : Etienne Desrochers
// Date : 2020-05-06
// But : Valide le numero de carte d'une américan express

function confirmeNumeroAmerican(Numero)
{
    //3 [4 ou 7] [13 chiffre]
    const regex = RegExp("3[47][0-9]{13}");
    //Valide le numero selon le patern
    return regex.test(Numero);



}