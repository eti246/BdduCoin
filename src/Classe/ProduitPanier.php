<?php
namespace App\Classe;
// Auteur : Etienne Desrochers
// Date : 2020-05-06
// But : Rajoute à l'entite le champs quantite

use App\Entity\Produits;

class ProduitPanier extends Produits
{       
    //Atribut
    public $quantite;

    //Constructeur
    public function __construct($Produit,$quantite)
    {
        
      
        $this->setId( $Produit->getId());
        $this->setNomProduit($Produit->getNomProduit());
        $this->setIdcategorie($Produit->getIdCategorie());
        $this->setDescription($Produit->getDescription());
        $this->setQuatitestock($Produit->getQuatitestock());
        $this->setQuantiteminimale($Produit->getQuantiteminimale());
        $this->setDc($Produit->getDc());
        $this->setPrix($Produit->getPrix());
        $this->quantite = $quantite;
    }
    
    //Accesseur
    public function getQuantite() {return $this->quantite;}


}
?>