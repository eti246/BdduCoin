function confirmation()
{
    //Va chercher les informations de la carte
    var numero = document.getElementById("numero").value;
    var date = document.getElementById("expiration").value;
    var dateSystem = new Date();

    var dateNew = new Date();
    dateNew.setFullYear(date.substring(0,4));
    dateNew.setMonth(date.substring(5,7));
    dateNew.setDate(date.substring(9,10));

 // Regarde pour une MasterCard
 if(document.getElementById("carte1").checked)
 {
     if(confirmeNumeroMaster(numero))
     {
         if(dateSystem.getTime() < dateNew.getTime())
         {
             return true;
         }
         else
         alert("La carte de credit est expiré");
     }
     alert("Numero de Carte Invalide");

 }
    // Regarde pour une Visa
    if(document.getElementById("carte2").checked)
    {
        if(confirmeNumeroVisa(numero))
        {
            if(dateSystem.getTime() < dateNew.getTime())
            {
                return true;
            }
            else
            alert("La carte de credit est expiré");
        }
        else
            alert("Numero de Carte Invalide");
        
    } 
    // Regarde pour une American Express
    else if(document.getElementById("carte3").checked)
    {
        if(confirmeNumeroAmerican(numero))
        {
            if(dateSystem.getTime() < dateNew.getTime())
            {
                return true
            }
            else
            alert("La carte de credit est expiré");
        }
        else
            alert("Numero de Carte Invalide");
    }
    // Aucune Carte Selectioné
    else
        alert("Veuiller selectionner votre type de Carte de Crédit");

    return false;
}


function confirmeNumeroMaster(Numero)
{
    // 5 [1 ou 2 ou 3 ou 4 ou 5] [14 Chiffre]
    const regex = RegExp("5[1-5][0-9]{14}");
    return regex.test(Numero);
}
function confirmeNumeroVisa(Numero)
{
    // 4 [13 chiffre] [3 chiffre optionnel]
    const regex = RegExp("4[0-9]{12}(?:[0-9]{3})?");
    return regex.test(Numero);
}
function confirmeNumeroAmerican(Numero)
{
    //3 [4 ou 7] [13 chiffre]
    const regex = RegExp("3[47][0-9]{13}");
    return regex.test(Numero);
}

