<?php 
namespace App\Classe;


class Produit
{
    // Attribut
    public $id;
    public $nom;
    public $description;
    public $prix;
    public $quantiteInventaire;
    public $quantiteSeuil;
    public $idCategorie;
    public $dc;

    // Contructeur
    public function __construct($id,$nom,$description,$prix,$quantiteInventaire,$quantiteSeuil,$idCategorie,$dc)
    {
        $this->id = $id;
        $this->nom = $nom;
        $this->description = $description;
        $this->prix = $prix;
        $this->quantiteInventaire = $quantiteInventaire;
        $this->quantiteSeuil = $quantiteSeuil;
        $this->idCategorie = $idCategorie;
        $this->dc = $dc;
    }

    // Accesseur
    public function getId() {return $this->id;}
    public function getNom() {return $this->nom;}
    public function getDescription() {return $this->description;}
    public function getPrix() {return $this->prix;}
    public function getQuantiteInventaire() {return $this->quantiteInventaire;}
    public function getQuantiteSeuil() {return $this->quantiteSeuil;}
    public function getIdCategorie() {return $this->idCategorie;}
    public function getDc() {return $this->dc;}
}


?>