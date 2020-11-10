<?php
namespace App\Controller;
// Auteur : Etienne Desrochers
// Date : 2020-05-06
// But : Controle du Catalogue
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Produits;
use App\Entity\Catagorie;
use Symfony\Component\HttpFoundation\Session\Session;
class menuController extends AbstractController
{
    /**
     * @Route("/",name="menu")
     */
    public function menuAction(Request $req)
    {    


        // Va chercher la Session
        $session = $req->getSession();
       
        //regarde si le Panier n'existe pas dans la session
        if($session->get('Panier') ==null)
        {   
            // Place dans la session le Panier et le nombre d'item
            $session->set('Panier',[]);
            $session->set('NombreItem',0);
        }

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
     /**
     * @Route("menuContact",name="menuContact")
     */
    public function menuContactAction()
    {
        //Affiche la page de Contact
        return $this->render("menuContact.html.twig");
    }

     /**
     * @Route("recherche/{id}",name="recherche")
     */
    public function rechercheAction($id)
    {
       //Va chercher toute les categories
        $categorie = $this->getCategorie();
       
        // Va chercher les produits qui contienne l'id de produit valide
        if(isset($_POST['texte']))
            // Va chercher les produits qui contiènne le texte
            $produit = $this->getProduitTexte($_POST['texte']);
        
        else
            // Va chercher touts les produits
            $produit = $this->getProduit($id);
        // Affiche la page
        return $this->render('menu.html.twig', ['tabCategorie'=>$categorie, 'tabProduit'=>$produit]);	
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
    // Retourne les produit qui contienne un un texte passer en paramêtre
    //------------------------------------
    public function getProduitTexte($texte)
    {
         // Connection avec la bd 
         $em = $this->getDoctrine()->getManager(); 
        
        if($texte =="")
            $produit = $em->getRepository(Produits::class)->findAll(); 
        
        else
        {
            $Prod = $em->getRepository(Produits::class)->findAll();
            $produit = [];
            foreach ($Prod as $item)
            {
                if(strpos($item->getNomproduit(),$texte))
                    array_push( $produit,$item);   
            }
        }
       
        return $produit;
       
    }
}
?>