<?php
namespace App\Controller;
// Auteur : Etienne Desrochers
// Date : 2020-05-06
// But : Permet les commandes

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

use App\Entity\Catagorie;

use App\Entity\Produits;
use App\Entity\Commande;
use App\Entity\CommandeDetail;
use App\Entity\Compte;

class commandeController extends AbstractController 
{
  
    /**
     * @Route("menuCommande",name="menuCommande")
     */
    public function menuCommandeAction(Request $req)
    {
        // Va chercher la SESSION
        $session = $req->getSession();

        // Va chercher le panier
        $panier = $session->get('Panier',[]);
        
        return $this->render("Commande/menu.html.twig",['produits' => $panier, 'totalBrute'=>$session->get('TotalBrute')]);
    }

    /**
     * @Route("menuInfoCommande",name="menuInfoCommande")
     */
    public function menuInfoCommandeAction(Request $req)
    {
        //Affiche les information de la commande
        return $this->render("Commande/menuSaisiInfo.html.twig");
    }

    /**
     * @Route("menuPreparation",name="menuPreparation")
     */
    public function menuPreparationAction(Request $req)
    {
        // Connection avec la bd 
        $em = $this->getDoctrine()->getManager();
        // Inscrire en Bd la commande
        $idCommande = $this->inscrireEnBd($em,$req);
        //Mettre A jour les quantités
        $this->metreAJourQuantite($em,$idCommande,$req);
        //Va chercher les produit en Rupture
        $enRupture = $this->getProduitEnRupture($em,$idCommande);
        //Vider le panier
        $this->viderPanier($req);
        return $this->render("Commande/menuPreparationCommande.html.twig", ['produitEnRuputure'=>$enRupture, 'idCommande'=>$idCommande]);
    }
    /**
     * @Route("menuAfficherCommande",name="menuAfficherCommande")
     */
    public function menuAfficherCommandeAction(Request $req)
    {
        //Connecte à la bd
        $em = $this->getDoctrine()->getManager();
        $session = $req->getSession();

       // Va chercher tout les commande
        $commandeAll = $em->getRepository(Commande::class)->findAll();
        $commande = [];
        foreach($commandeAll as $commandeTemp)
        {
            // Valide que els commande soit au nom de l'utilisateur
            if($commandeTemp->getClient()->getNomUtilisateur() == $session->get('NomUtilisateurConnecter') )
            {
                // Rajoute la commande dans la liste
                $this->setTotal($em,$commandeTemp->getId());
                $commandeTemp->setTemp(1);
                if(!empty($commande))
                {
                    
                    if($commandeTemp->getDateCommande()->getTimeStamp() > $commande[0]->getDateCommande()->getTimeStamp() )
                    {
                        array_unshift($commande,$commandeTemp);   
                    }
                    else
                        array_push($commande,$commandeTemp);

                }
                else
                {
                    array_push($commande,$commandeTemp);
                }

              
            }
        }   
        return $this->render("Commande/menuAfficherCommande.html.twig",['commande'=>$commande]);
    }
    /**
     * @Route("annulerCommande/{id}",name="annulerCommande")
     */
    public function annulerCommande($id)
    {
        return $this->render("Commande/annulerCommande.html.twig",['id'=>$id]);
    }

    /**
     * @Route("commandeAnnuler/{id}",name="commandeAnnuler")
     */
    public function commandeAnnuler($id)
    {
        // Annule la commande
        $this->annulerCommandeModifierQuantite($id);
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
    //----------------------
    //Function qui annule une commande
    //----------------------
    private function annulerCommandeModifierQuantite($id)
    {
        //Connecte à la bd
        $em = $this->getDoctrine()->getManager();
        // Va chercher la commande
        $commande =$em->getRepository(Commande::class)->find($id);

        //Va chercher les details 
        $commandeDetail = $em->getRepository(CommandeDetail::class)->findAll();

        foreach ($commandeDetail as $item)
        {
            // Valide que le detail fait partit de la commande
            if($item->getCommande()->getId() ==$id )
            {
                // Enlève le detail de la commande
                $value = $item->getProduit()->getQuatitestock();
                $value2 =$item->getQuantite();
                if($value2!=0)
                    $item->getProduit()->setQuatitestock( $value+ $value2 );
                $em->remove($item);
            }
            
        }
        //Enlève la commande
        $em->remove($commande);
        $em->flush();
    }
    //------------------------------
    // Function qui inscrit en Bd la commande
    //------------------------------
    private function inscrireEnBd($em,$req)
    {
        //Va chercher la session
        $session = $req->getSession();
        // Va chercher le compte
        $compte = $this->retourCompte($session->get("NomUtilisateurConnecter"),$req);
        // Créé une nouvelle Commande en Bd
        $commande = new Commande();
        // Place l'id du client dans la commande
        $commande->setClient($compte);
        //Place la date de la commande dans l'entity
        $commande->setDateCommande(new \DateTime());
        //Place la commande en Bd
        $em->persist($commande);
        
        // Description
        //Pour chaque Produit dans le panier
        foreach ($session->get("Panier") as $item)
        {
            //Créé une nouvelle CommandeDetail
            $detail = new CommandeDetail();
            // Place la commande dans l'entity
            $detail->setCommande($commande);
            //Place le Produit dans l'entity
            $detail->setProduit($em->getRepository(Produits::class)->find($item->getId()));
            // Regarde si on a asser du produit 
            if($item->getQuantite() <= $item->getQuatitestock())
            {
                // Plance la quantite demander dans le Champs
                $detail->setQuantite($item->getQuantite());
                // Le Produit n'est pas en rupture
                $detail->setQuantiteRupture(0);
            }
            else
            {
                $detail->setQuantite($item->getQuatitestock());
                $detail->setQuantiteRupture($item->getQuantite()-$item->getQuatitestock());
            }
            $em->persist($detail);
        }


       
        $em->flush();
        return $commande->getId();
    }

    //----------------------
    //  Function qui met à jour les quantites
    //----------------------
    private function metreAJourQuantite($em,$idCommande,$req)
    {
        // Va chercher les detail de la commande
        $detail = $this->getDetailCommande($em,$idCommande);

        foreach($detail as $item)
        {
            // Va chercher le produit
            $produit = $item->getProduit();
            // Valide qque le Produit n'Est pas en rupture
            if($item->getQuantiteRupture() ==0)
                //Ajuste la Quantite
                $item->getProduit()->setQuatitestock($item->getProduit()->getQuatitestock() - $item->getQuantite());
            
            else
                $item->getProduit()->setQuatitestock(0);
        }
            
            $em->flush();

    }
    //----------------------
    // Function qui va chercher les produits en rupture
    //----------------------    
    private function getProduitEnRupture($em,$idCommande)
    {   
        //Va contenir les produits en ruptures
        $listRupture = [];

        
        // Va chercher les 
        $detailAll = $em->getRepository(CommandeDetail::class)->findAll();
        foreach($detailAll as $detail)
        {
           // Regarde si il est en rupture
           if($detail->getQuantiteRupture() and $detail->getCommande()->getId() == $idCommande)
           {
               // Le rajoute à la liste
               $Produit = $detail->getProduit();
               $Produit->setQuatitestock($detail->getQuantiteRupture());
               array_push($listRupture,$Produit);
           }
        }

        return $listRupture;

    }
    //----------------------
    // Function qui va chercher les detail d'une commande
    //----------------------    
    private function getDetailCommande($em,$idCommande)
    {
        
        // Va chercher tout les details
        $detailAll = $em->getRepository(CommandeDetail::class)->findAll();

        $detailList= [];
        // Pour Chaque Detail
        foreach($detailAll as $detail)
        {
            // Valide le detail
            if($detail->getCommande()->getId() == $idCommande)
            {   
                //Le rajoute à la liste
                array_push($detailList,$detail);
            }
        }
        return $detailList;
    }
    

    //------------------------------
    // Function qui vide le panier
    //------------------------------
    private function viderPanier($req)
    {
        //Va chercher la session
        $session = $req->getSession();
        // Vide le panier
        $session->set('Panier',[]);
        $panier=[];
        //Place le nombre d'item
        $session->set('NombreItem',0);
    }

    //------------------------------
    // Function qui retourne le compte de L'utilisateur
    //------------------------------
    private function retourCompte($nomCompte,$req)
    {
        //Va chercher la session
        $session = $req->getSession();

         // Connection avec la bd 
        $em = $this->getDoctrine()->getManager(); 
        
        // va chercher les comptes
        $compte = $em->getRepository(Compte::class)->findAll();

        // Pour chaque Compte
        foreach ($compte as $temp)
        {
            // Valide le compte
            if($session->get("NomUtilisateurConnecter") == $temp->getNomUtilisateur())
            {
                //Retourne le compte
               return $temp;
            }    
        }
    }
    //----------------------
    // Function qui place le total
    //----------------------    
    private function setTotal($em,$idCommande)
    {

        $total =0;
        // Va chercher la Commande et ses details
        $detailAll = $em->getRepository(CommandeDetail::class)->findAll();
        $commande = $em->getRepository(Commande::class)->find($idCommande);
        foreach($detailAll as $detail)
        {
           // Regarde si il est en rupture
           if($detail->getCommande()->getId() == $idCommande)
           //Rajout au total
                $total = $detail->getProduit()->getPrix() * ($detail->getQuantite() + $detail->getQuantiteRupture());
           
        }
        // Place le total
        $commande->setTotal($total);    
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
}