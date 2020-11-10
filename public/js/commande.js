// Auteur : Etienne Desrochers
// Date : 2020-05-06
// But : Contient la fonction qui confirme la carte de credit

function confirmation()
{


//Va chercher les informations de la carte
var numero = document.getElementById("numero").value;
var date = document.getElementById("expiration").value;
var dateSystem = new Date();

// Va chercher la date d'expiration
var dateNew = new Date();
dateNew.setFullYear(date.substring(0,4));
dateNew.setMonth(date.substring(5,7));
dateNew.setDate(date.substring(9,10));

    // Regarde pour une MasterCard
    if(document.getElementById("carte1").checked)
    {
        //Confirme le numero
        if(confirmeNumeroMaster(numero))
        {   
            //Valide la date d'expiration
            if(dateSystem.getTime() < dateNew.getTime())
            {
                //La carte est valide
                return true;
            }
            else
                //La carte n'est pas valide
                alert("La carte de credit est expiré");
        }
        alert("Numero de Carte Invalide");

    }
    // Regarde pour une Visa
    else if(document.getElementById("carte2").checked)
    {
        //Confirme le numero
        if(confirmeNumeroVisa(numero))
        {
            //Confirme la date d'expiration
            if(dateSystem.getTime() < dateNew.getTime())
            {
                // la carte est valide
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
        //Valide le numero 
        if(confirmeNumeroAmerican(numero))
        {
            // Valide la date d'expiration
            if(dateSystem.getTime() < dateNew.getTime())
            {
                // La carte est valide
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

        //La carte n'est pas valide
    return false;
}