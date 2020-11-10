<?php
namespace App\Controller;
// Auteur : Etienne Desrochers
// Date : 2020-05-06
// But : Controle le Panier
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Classe\Panier;
use App\Entity\Produits;
use App\Classe\ProduitPanier;
use App\Classe\menuController;
use App\Entity\Catagorie;
use Symfony\Component\HttpFoundation\Session\Session;
// Lien : https://www.youtube.com/watch?v=_tWL-QDFuQ4

class panierController extends AbstractController 
{
  
    /**
     * @Route("menuPanier",name="menuPanier")
     */
    public function menuPanierAction( Request $req)
    {
        
        // Regarde si le _POSTE contient un modification de quanite
        if(isset($_POST['quantite']) and isset($_POST['idItem']))
            //Modifie la quantite 
            $this->modifierQuantiter($req,$_POST['idItem'],$_POST['quantite']);

        // Va chercher la SESSION
        $session = $req->getSession();

        // Va chercher le panier
        $panier = $session->get('Panier',[]);

        // Calcule le cout et le met dans la Session
        $this->setCout($req);

        //Affiche le panier
        return $this->render('panier.html.twig',['produits' => $panier, 'totalBrute'=>$session->get('TotalBrute')]);	
    }
     /**
     * @Route("viderPanier",name="viderPanier")
     */
    public function menuViderPanierAction( Request $req)
    {
        //Va chercher la session
        $session = $req->getSession();
        // Vide le panier
        $session->set('Panier',[]);
        $panier=[];
        //Place le nombre d'item
        $session->set('NombreItem',0);
        //Affiche le panier
        return $this->render('panier.html.twig',['produits' => $panier ] );	
    }

       /**
     * @Route("enleverProduitPanier/{id}",name="enleverProduitPanier")
     */
    public function enleverProduitPanierAction(Request $req,$id)
    {
        // Regarde si le _POSTE contient un modification de quanite
        if(isset($_POST['quantite']) and isset($_POST['idItem']))
            //Modifie la quantite 
            $this->modifierQuantiter($req,$_POST['idItem'],$_POST['quantite']);
            
        // Va chercher la session
        $session = $req->getSession();
        // Va chercher le panier
        $panierSession = $session->get('Panier',[]);
        //Créé un nouvel object panier

        $panier = new Panier($panierSession);
        
        //Suprime le produit
        $panier->suprimerProduit($id);

        //Place dans la session les nouvelles valeur de Panier et de Nombre de Produit
        $session->set('Panier',$panier->panier);
        $session->set('NombreItem',$panier->nombreProduit());
        // Place le cout 
        $this->setCout($req);

        //Affiche le panier
        return $this->render('panier.html.twig',['produits' => $session->get('Panier') ,  'totalBrute'=>$session->get('TotalBrute')]);	
    }

    //---------------------
    // Modifie la quantite 
    //--------------------- 
    public function modifierQuantiter(Request $req,$id,$quantite)
    {
        // Va chercher la session
        $session = $req->getSession();
        // Va chercher le panier dans la session
        $panierSession = $session->get('Panier');
        //Créé un nouvel object panier
        $panier = new Panier($panierSession);
       
        // Si la quantite est de 0 on suprime le produit
        if($quantite ==0)
            // Suprime le produit
            $panier->suprimerProduit($id);
        else
            // Change la quantite du produit
            $panier->panier[$id]->quantite = $quantite;

        //Place dans la session les nouvelles valeur de Panier et de Nombre de Produit 
        $session->set('Panier',$panier->panier);
        $session->set('NombreItem',$panier->nombreProduit());
        // Place le cout 
        $this->setCout($req);
    }
  

     
   /**
     * @Route("addProduit/{id}",name="addProduit")
     */
    public function addProduitAction($id,Request $req)
    {   

        // Va chercher le produit
        $produit = $this->retourProduit($id);
        $this->placeProduitDansPanier($produit,$req);
        
        
        
        // Va chercher toute les categorie
        $categorie = $this->getCategorie();
         // Verifie que la valeur de recherche est définie 
          if(isset($_POST['texte']))
              // Va chercher les produits qui contiènne le texte
              $produit = $this->getProduitTexte($_POST['texte']);
          
          else
              // Va chercher touts les produits
              $produit = $this->getProduit(0);
        
       
        // Affiche la page menu.html.twig
        return $this->render('menu.html.twig', ['tabCategorie'=>$categorie, 'tabProduit'=>$produit]);	
    }

    //---------------------------
    // Retourne un Produit
    //---------------------------
    public function retourProduit($id)
    {
        // Connection avec la bd 
        $em = $this->getDoctrine()->getManager(); 

        return $em->getRepository(Produits::class)->find($id);
        
    }

    
    //------------------------------------
    // Fonction qui retourne les catégorie
    //------------------------------------
    public function getCategorie()
    {
        // Connection avec la bd 
        $em = $this->getDoctrine()->getManager(); 
        
        //Retourne les categories
        return $em->getRepository(Catagorie::class)->findAll(); 
    }

    //------------------------------------
    // Fonction qui retourne les produit
    //------------------------------------
    public function getProduit($id)
    {
       // Connection avec la bd 
       $em = $this->getDoctrine()->getManager(); 
       // Si $id est = 0 on va chercher tous les produit
       if($id ==0)
           // Va chercher les information dans la bd
           $produit = $em->getRepository(Produits::class)->findAll(); 
       
       else
       {
           // Va chercher les information dans la bd
           $Prod = $em->getRepository(Produits::class)->findAll(); 
           $produit = [];
           foreach ($Prod as $item)
           {
               if($item->getIdcategorie() == $id)
               array_push($produit, $item);
           }

       }
       
       //Retourne la liste des produits
       return $produit;
    }
    
    //------------------------------------
    // Place un Produit dans le panier
    //------------------------------------
    public function placeProduitDansPanier($produit, Request $req)
    {   
       
        // Va chercher la Session
        $session = $req->getSession();
        //Va chercher le Panier dans la Session
        $panierSession = $session->get('Panier',new Panier([]));

        // Regarde si le panier de la session est vide
        if(!empty($panierSession))
            $panier = new Panier($panierSession);
        else
            $panier=new Panier([]);
        
        //Rajoute un produit dans le panier

        $panier->rajouterProduit($produit);
        //Place dans la session les nouvelles valeur de Panier et de Nombre de Produit 
        $session->set('Panier',$panier->panier);
        $session->set('NombreItem',$panier->nombreProduit());
    }

    
    //------------------------------------
    // Calcul et met le cout avant taxe dans la session
    //------------------------------------
    public function setCout(Request $req)
    { 
        //Va chercher la session
        $session = $req->getSession();
        // Va chercher le panier dans la session
        $panierSession = $session->get('Panier',new Panier([]));
        //Regarde si la panier est vide
        if(!empty($panierSession))
            $panier = new Panier($panierSession);
        else
            $panier= new Panier([]);
        //Place le total brute dans la session
        $session->set('TotalBrute',$panier->calculerTotal());
    }
}
?>