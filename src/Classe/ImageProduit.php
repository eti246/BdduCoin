<?php
//Auteur : Etienne Desrochers
//Date: 5/24/2020
//But : Contient la classe Gérant les images des produits
namespace App\Classe; 

use Symfony\Component\HttpFoundation\File\UploadedFile; 

class ImageProduit
{

    //Variable contenand les images
    private $imageProduit1; 
    private $imageProduit2;     


    public function getImageProduit1() : ?UploadedFile
    {
        return $this->imageProduit1;
    }

    public function getImageProduit2() : ?UploadedFile
    {
        return $this->imageProduit2;
    }

    public function setImageProduit1 (UploadedFile $fichier = null)
    {
        $this->imageProduit1 = $fichier;
    }
    public function setImageProduit2 (UploadedFile $fichier = null)
    {
        $this->imageProduit2 = $fichier;
    }

    //----------------------------
    //Function qui téléverse une image sur le serveur sous le soumis
    //----------------------------
    public function televerser($id,&$codeErreur = 0)
    {
        //Aucune erreur 
        $codeErreur =0;
        //Va chercher les extentions des images
        $type1 = $this->imageProduit1->getClientMimeType(); 
        $type2 = $this->imageProduit2->getClientMimeType();
 
        // On valide les extensions
        if (($type1 == 'image/jpeg' or $type1 == 'image/png' or $type1 == 'image/gif' ) 
            and ($type2 == 'image/jpeg' or $type2 == 'image/png' or $type2 == 'image/gif' ))
        {
            //Nom pour la deuxième image
            $temp=$id."_1";
            //Contient le dossier contenant les images
            $nomDossier= __DIR__ . '/../../public/Comic';
            //Contient les noms des images
            $nomFichier1 = "prod$id.jpg"; 
            $nomFichier2 = "prod$temp.jpg"; 
 
            //Place les images dans le fichier
            $this->imageProduit1->move($nomDossier,$nomFichier1);
            $this->imageProduit2->move($nomDossier,$nomFichier2);
            return true;
        }
        else
        {
            // -3 voulant dire mauvaise extension de fichier
            $codeErreur = -3;
            return false; 
        }
    }

}
?>