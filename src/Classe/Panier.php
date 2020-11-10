<?php 
namespace App\Classe;
// Auteur : Etienne Desrochers
// Date : 2020-05-06
// But : Contient la class panier qui permet les manipulation du panier

use App\Classe\ProduitPanier;



use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\Session\Session;

//****************************************************** 
// Classe permetant de faire les manipulations nécésaire
// pour le bon fonctionnement du panier
//******************************************************
class Panier 
{
    //Atribut array de ProduitPanier
    public $panier=[];
    
    //Constructeur 
    public function  __construct($panierSession)
    {

        //Verifie si le panier de session est vide
        if(!empty($panierSession))
        {
            foreach ($panierSession as $item)
            {

                $this->rajouterProduit($item);
                $this->panier[$item->getId()]->quantite=$item->quantite; 
            }
        }
       
    }
    

    //---------------------------
    // Rajoute un Produit dans le panier
    //---------------------------
    public function rajouterProduit($produit)
    {
        
        
        // Regarde si le panier contient l'objet augmente la quantite
        if(array_key_exists($produit->getId(),$this->panier) and $this->panier[$produit->getId()]->quantite != $produit->getQuatitestock())
            //Incremente la quantite
            $this->incrementerQuantite($produit->getId());
        
        
        //Sinon rajoute l'item dans le panier
        else if(!array_key_exists($produit->getId(),$this->panier))
        {            
           
           
            // Créé un nouveau produit
            $unProduit = new ProduitPanier($produit,1);
            //Rajoute le produit dans le panier
            $this->panier[$produit->getId()] = $unProduit;
        }
        
        
    }

    //---------------------------
    // Affiche le panier
    // Fonction de test
    //---------------------------
    private function montrePanier()
    {
        // Si vide 
        if(empty($this->panier))
            //Affiche vide
            var_dump('vide');
        
        else
        {
            //Pour chaque element
            foreach ($this->panier as $item)
            {   
                //affiche le produit
                var_dump($item);
            }
        }
        //Met fin au programme
        die();
    }

    //---------------------------
    // Retourne le nombre de produit dans le panier
    //---------------------------
    public function nombreProduit()
    {
        //Commence le compteur à 0
        $compteur = 0;
        //Pour chaque produit
        foreach ($this->panier as $item)
            //Rajoute la quantite du produit
            $compteur += $item->quantite;
        //Retourne le compteur
        return $compteur;
    }
    //---------------------------
    // Supprime un produit dans le Panier
    //---------------------------
    public function suprimerProduit($id)
    {
        //Suprime le Prosuit
        unset($this->panier["$id"]);
    }
    //---------------------------
    // Augmente la quantite d'un produit de 1
    //---------------------------
    public function incrementerQuantite($id)
    {
        //Augemente la quantite
        $this->panier[$id]->quantite +=1;
    }
    //---------------------------
    // Suprime un produit dans le panier
    // En réduisant la quantite
    //---------------------------
    public function suprimerUnProduit($id)
    {
        //Vérifie que le produit est plus qu'un en quantité 
        if($this->panier[$id]->quantite!=2)
            $this->panier[$id]->quantite -=1;
        // Sinon on supprime (1 -1 = 0 produit)
        else
            //On suprime le produit
            $this->suprimerProduit($id);
    }
    //---------------------------
    // Calcul la valeur total du panier
    //---------------------------
    public function calculerTotal()
    {
        //Commence le total à 0
        $total=0;
        // Vérifie que le panier n'est pas vide
        if(!empty($this->panier))
        {
            // Pour chauque Produit dans le panier
            foreach($this->panier as $item)
                //Rajoute au total la valeur du produit * son nombre de fois dans le panier
                $total += $item->quantite * $item->getPrix() ;
        }
        //Retourne le total
        return $total;
        
    }
    //---------------------------
    // Calcul La TPS
    //---------------------------
    public function calculerTPS() {return $this->calculerTotal()*0.05;}
    //---------------------------
    //Calcul la TVQ
    //---------------------------
    public function calculerTVQ(){return $this->calculerTotal()*0.09975;}
    //---------------------------
    // Calcul le Total avec Taxe
    //---------------------------
    public function calculerTotalAvecTaxe(){return $this->calculerTVQ()+ $this->calculerTPS()+ $this->calculerTotal();}
}


?>