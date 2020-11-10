<?php
namespace App\Controller;
// Auteur : Etienne Desrochers
// Date : 2020-05-21
// But : Permet les manipulations d'admin

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType; 

use App\Entity\Compte;
use App\Entity\Catagorie;
use App\Entity\Produits;
use App\Entity\Commande;
use App\Entity\CommandeDetail;

use App\Classe\ImageProduit;
use App\Form\PhotoProduitType;

class adminController extends AbstractController 
{
    /**
     * @Route("admin",name="admin")
     */
    public function menuCommandeAction(Request $req)
    {
     
        
        if(!$this->adminConnecter($req))
        {

            //Regarde si le POST est vide
            if(!empty($_POST))
            {
                //Valide le compte 
                if($this->valideCompteAdmin($_POST))
                {
                    $session = $req->getSession();
                    $session->set('adminConnecter',true);
                    return $this->render("Admin/connecter.html.twig");
                }
                
                //Affiche un message dans la page
                else
                echo('Erreur de Connection');
            }
            return $this->render("Admin/connection.html.twig");
        }
        else
            return $this->render("Admin/connecter.html.twig");
    }

     /**
     * @Route("admin/menu",name="adminmenu")
     */
    public function menuConnecterAdmin(Request $req)
    {
        if($this->adminConnecter($req))
            return $this->render("Admin/connecter.html.twig");
        else
            return $this->redirectToRoute('admin');
    }
    /**
     * @Route("admin/ajoutercategorie",name="ajoutercategorie")
     */
    public function menuAjouterCategorie(Request $req)
    {
        if($this->adminConnecter($req))
       {
          
            $erreur = false;
            //Valide que le POST n'est pas vide 
            if(!empty($_POST))
            {
                // Valide la categorie
                if($this->valideCategorie($_POST['nomCategorie']))
                //Rajoute la bd en catégorie 
                $this->rajouterCategorie($_POST['nomCategorie']);
                else
                {

                    //Il y a une erreur
                    $erreur =true;
                    $this->addFlash('notice', 'La catégorie est invalide');
                }
                
            } 
            return $this->render("Admin/ajouterCategorie.html.twig", ['categorie'=>$this->getCategorie(),'erreur'=>$erreur]);
        }
        else 
            return $this->redirectToRoute('admin');
    }

    /**
     * @Route("admin/modifierCategorie",name="modifierCategorie")
     */
    public function menuModifierCategorie(Request $req)
    {
        if($this->adminConnecter($req))
        {
            if(!empty($_POST))
            {
                $this->valideModifCategorie($_POST);
            }
            return $this->render("Admin/Modifier Categorie/modifierCategorie.html.twig",['categorie'=>$this->getCategorie()]);
        }
        else 
            return $this->redirectToRoute('admin');
    }

     /**
     * @Route("admin/ajouterProduit",name="ajouterProduit")
     */
    public function menuAjouterProduit(Request $req)
    {
        if($this->adminConnecter($req))
        {
            // Les varibles suivante seront true si le champs associé est invalide
            $valideNom = false;
            $valideDescription = false;
            $validePrix =false;
            $valideStock =false;
            $valideMinimal = false;
            
            if(!empty($_POST))
            {
                //valdie les champs
                if(!$this->valideString($_POST['nomProduit'],2,50)) 
                $valideNom = true;
                if(!$this->valideString($_POST['description'],2,500)) 
                $valideDescription = true;
                if($_POST['prix']< 0 or (empty($_POST['prix'] and $_POST['quantiteMinimal']!=0)))
                $validePrix = true;
                if($_POST['quantiteMinimal'] <0 or  (empty($_POST['quantiteMinimal'])and $_POST['quantiteMinimal']!=0) )
                $valideMinimal = true;
                if($_POST['quantiteStock'] < 0 or (empty($_POST['quantiteStock'])  and $_POST['quantiteStock']!=0) )
                $valideStock = true;
                
                //Si le produit est valide on le rajoute en bd
                if(!$valideNom and !$valideDescription and !$validePrix and !$valideStock and !$valideMinimal)
                {
                    $this->rajouterProduit($_POST);
                    return $this->render("Admin/connecter.html.twig");
                }
            }  
            return $this->render("Admin/Ajouter Produit/ajouterProduit.html.twig",['categorie'=>$this->getCategorie(),
            'valideNom'=>$valideNom, 
            'valideDescription'=>$valideDescription,
            'validePrix'=>$validePrix,
            'valideStock'=>$valideStock,
            'valideMinimal'=>$valideMinimal]);
        }
        else 
            return $this->redirectToRoute('admin');
    }  
    
     /**
     * @Route("admin/modifierProduit",name="modifierProduit")
     */
    public function menuModifierProduit(Request $req)
    {
        if($this->adminConnecter($req))
            return $this->render("Admin/Modifier Produit/modiferProduit.html.twig", ['produit'=>$this->getProduits()]);
        else
            return $this->redirectToRoute('admin');
    }

    /**
     * @Route("admin/modifProduit{id}",name="modifProduit")
     */
    public function menuModifProduit($id,Request $req)
    {
        if($this->adminConnecter($req))
        {

            // Connection avec la bd 
            $em = $this->getDoctrine()->getManager();
            
            // Les varibles suivante seront true si le champs associé est invalide
            $valideNom = false;
            $valideDescription = false;
            $validePrix =false;
            $valideStock =false;
            $valideMinimal = false;
            
            if(!empty($_POST))
            {
                //valdie les champs
                if(!$this->valideString($_POST['nomProduit'],2,50)) 
                $valideNom = true;
                if(!$this->valideString($_POST['description'],2,500)) 
                $valideDescription = true;
                if($_POST['prix']< 0 or (empty($_POST['prix'] and $_POST['quantiteMinimal']!=0) ))  
                $validePrix = true;
                if($_POST['quantiteMinimal'] <0 or (empty($_POST['quantiteMinimal'])and $_POST['quantiteMinimal']!=0) )
                $valideMinimal = true;
                if($_POST['quantiteStock'] < 0 or (empty($_POST['quantiteStock'])  and $_POST['quantiteStock']!=0) )
                $valideStock = true;
                
                
                //Si le produit est valide on le rajoute en bd
                if(!$valideNom and !$valideDescription and !$validePrix and !$valideStock and !$valideMinimal)
                {
                    
                    $this->modifProduit($_POST);
                    return $this->render("Admin/Modifier Produit/modiferProduit.html.twig", ['produit'=>$this->getProduits()]);
                }
            }
            
            
            return $this->render("Admin/Modifier Produit/modifProduit.html.twig",['produit'=>$em->getRepository(Produits::class)->find($id),
            'categorie'=>$this->getCategorie(),
            'valideNom'=>$valideNom, 
            'valideDescription'=>$valideDescription,
            'validePrix'=>$validePrix,
            'valideStock'=>$valideStock,
            'valideMinimal'=>$valideMinimal]);
        }
        else 
            return $this->redirectToRoute('admin');
    }

    /**
     * @Route("admin/modifierCategorie/modifcationImage{id}",name="modifierImage")
     */
    public function menuModifierImage($id,Request $req)
    {
        if($this->adminConnecter($req))
        {
            $imagesProduit= new ImageProduit;
            
            //On créé une nouvelle instance de formulaire PhotoProduitType
            // $formPhotoProduit = $this->get('form.factory')->create(PhotoProduitType::Class,$imagesProduit);
            $formPhotoProduit = $this->get('form.factory')
            ->create(PhotoProduitType::Class, $imagesProduit); 
            
            //Valide Le formulaire
            $formPhotoProduit->handleRequest($req);
            if ($formPhotoProduit->isSubmitted())
            {
                //Valide les information fournit
                if ($formPhotoProduit->isValid())
                {
                    $codeErreur = 0;
                    if($imagesProduit->getImageProduit1()->getSize() <=100000 and $imagesProduit->getImageProduit2()->getSize() <=100000)
                    {

                        
                        //Code d'erreur, si il y a une erreur
                        //Téléverse les images
                        if ($imagesProduit->televerser($id,$codeErreur))
                        {
                            //Notice de réussite
                            $this->addFlash('notice', 'image du produit téléversée avec succès');
                            return $this->redirectToRoute('adminmenu');
                        }
                        else
                        // Notice d'échec
                        $this->addFlash('notice', "Erreur ($codeErreur) lors du téléversement de l'image");
                    }
                    else
                        $this->addFlash('notice', "Erreur ($codeErreur) la taille du fichier est trop grande");
                } 
                else
                {   //Affiche l'erreur
                    $msg = "Au moins une erreur. Veuillez corriger et revalider";
                    $this->addFlash('notice', $msg);
                }
            } 
            
            return $this->render("Admin/Modifier Produit/modifImage.html.twig",array('formPhotoProduit' => $formPhotoProduit->createView()));
        }
        else
            return $this->redirectToRoute('admin');
    }

    
    /**
     * @Route("admin/afficherCatalogue",name="afficherCatalogue")
     */
    public function menuAfficherCatalogue(Request $req)
    {
        if($this->adminConnecter($req))
            return $this->render("Admin/Afficher Catalogue/afficherCatalogue.html.twig",['produit'=>$this->getProduits()]);
        else
            return $this->redirectToRoute('admin');
    }

    
    /**
     * @Route("admin/rapportdesvente",name="rapportdesvente")
     */
    public function menuRapportDesVente(Request $req)
    {
        if($this->adminConnecter($req))
        {

            //Connecte à la bd
            $em = $this->getDoctrine()->getManager();
            
            // Va chercher tout les commande
            $commandeAll = $em->getRepository(Commande::class)->findAll();
            $commande = [];
            $totalCommande =0;
            foreach($commandeAll as $commandeTemp)
            {
                // Rajoute la commande dans la liste
                $totalCommande += $this->setTotal($em,$commandeTemp->getId());
                $commandeTemp->setTemp(1);
                if(!empty($commande))
                {
                    
                    if($commandeTemp->getDateCommande()->getTimeStamp() > $commande[0]->getDateCommande()->getTimeStamp() )
                    {
                        //Place la commmande au debut de la liste
                        array_unshift($commande,$commandeTemp);   
                    }
                    else
                    //Place la commande a la fin
                    array_push($commande,$commandeTemp);
                    
                }
                else
                {
                    //Place la commande a la fin
                    array_push($commande,$commandeTemp);
                }
                
            }
            
            return $this->render("Admin/Rapport Des Vente/rapportDesVentes.html.twig",['commandes'=>$commande,'total'=>$totalCommande]);
        }
        else
            return $this->redirectToRoute('admin');
    }
        
        /**
     * @Route("admin/produitACommander",name="produitACommander")
     */
    public function menuProduitACommender(Request $req)
    {
        
        if($this->adminConnecter($req))
            return $this->render("Admin/Produit Commander/produitACommander.html.twig",['produit'=>$this->getProduitDessousMinimun()]);
        else
            return $this->redirectToRoute('admin');
    }
     /**
     * @Route("admin/deconnexion",name="deconnexion")
     */
    public function menuDeconnexion(Request $req)
    {
        if($this->adminConnecter($req))
        {
            $session = $req->getSession();
            $session->set('adminConnecter',false);
            return $this->render("Admin/connection.html.twig");
        }
        else
        return $this->redirectToRoute('admin');
    }


    //-------------------
    // Function qui retourne les produit dont le stock est inferieur 
    // à la quantite minimale recommender
    //-------------------
    public function getProduitDessousMinimun()
    {
        //Array qui va contenir la liste des produit rechercher
        $arrayProduit =[];

        //Va chercher tout les produits
        $allProduit = $this->getProduits();

        //Pour chaque produit
        foreach($allProduit as $item)
        {
            //Si la quantite en stonck est inferieur a la quantite minimale
            if($item->getQuatitestock() < $item->getQuantiteminimale())
                array_push($arrayProduit, $item);

        }
        //Retourne les produits recherchers
        return $arrayProduit;
    }

    //--------------------
    // Function qui retourne true 
    // si le compte est valide (est l'adminisatrateur)
    //--------------------
    public function valideCompteAdmin($contenuPost)
    {
        // Va chercher le compte admin
        $compteAdmin = $this->getCompteAdmin();

        return ($contenuPost['userConnection'] == $compteAdmin->getNomUtilisateur() and
            password_verify($contenuPost['mpConnection'], $compteAdmin->getMotPasse()));
    }
    //--------------------
    // Retourne le compte Administrateur
    //--------------------
    public function getCompteAdmin()    
    {
        // Connection avec la bd 
        $em = $this->getDoctrine()->getManager();
        //Va chercher tout les comptes
        $compteAll = $em->getRepository(Compte::class)->findAll();

        //Pour Chaque Compte
        foreach($compteAll as $compte)
        {
            //Valide si le compte est l'admin
            if($compte->getNomUtilisateur() == "admin")
                //Retourne le compte de de l'admin
                return $compte; 
        }

    }

    //-----------------------
    // Function qui retourne les categories
    //-----------------------
    public function getCategorie()
    {
        // Connection avec la bd 
        $em = $this->getDoctrine()->getManager();
        //Retourne les catégorie
        return $compteAll = $em->getRepository(Catagorie::class)->findAll();
    }
    //-------------------------------
    // Function qui retourne true si la string donné
    // Contient un certaint nombre de caractère
    //-------------------------------
    public function valideString($texte, $min,$max)
    {
        // regarde si la string est vide 
        if(empty($texte))  
            return false;
        //Valide la string
        return (strlen($texte) >=$min and strlen($texte) <= $max);
    }

    //----------------------------
    // Function qui retourne true si la categorie existe déjas
    //----------------------------
    public function categorieExiste($texte)
    {
        //Va chercher les catégories
        $categorie = $this->getCategorie();

        //Pour chaque catégorie
        foreach($categorie as $item)
        {   
            //Valide le nom de la catégorie
            if($item->getNom() == $texte)
                //La catégorie existe
                return true;
        }
        //La catégorie n'existe pas
        return false;
    }

    //-------------------------
    //Valide la categorie
    //-------------------------
    public function valideCategorie($texte)
    {
        // Valide que la catégorie n'existe pas et que la string est valide
        return !$this->categorieExiste($texte) and $this->valideString($texte,2,25);
    }

    //-------------------------------
    // Rajoute une catégorie en bd
    //-------------------------------
    public function rajouterCategorie($texte)
    {
        // Connection avec la bd 
        $em = $this->getDoctrine()->getManager();
        //Créé une nouvelle catégorie
        $nouvCategorie = new Catagorie;
        // Donne le nom à la catégorie
        $nouvCategorie->setNom($texte);
        // Inscrit en Bd la catégorie
        $em->persist($nouvCategorie);
        $em->flush();
    }
    //-----------------------
    // Valide que des categorie sont a modifier
    //-----------------------
    public function valideModifCategorie($modif)
    {
        // Connection avec la bd 
        $em = $this->getDoctrine()->getManager();
        //Va chercher toute les catégorie
        $categorie = $em->getRepository(Catagorie::class)->findAll();
        //Compteur pour se situé dans les produit
        $compteur=0;
        // On enlève le Soumettre dans le POST
        array_pop($modif);
        //Pour chaque Categorie
        foreach($modif as $item)
        {
            //valide la catégorie
            if($this->valideCategorie($item))
            {
                // Change le nom de la catégorie
                $categorie[$compteur]->setNom($item);
            }
            //Incrementation du compteur
            $compteur++;
        }
        // Fait les changements en BD
        $em->flush();
    }

    //----------------------
    // Fonction qui rajoute en bd un produit
    //----------------------
    public function rajouterProduit($contenu)
    {   
        // Connection avec la bd 
        $em = $this->getDoctrine()->getManager();
        //Créé un nouveau produit
        $nouvProduit = new Produits;
        // Donne au nouveau produit ses informations
        $nouvProduit->setNomProduit($contenu['nomProduit']);
        $nouvProduit->setDescription($contenu['description']);
        $nouvProduit->setIdCategorie($contenu['categorie']);
        $nouvProduit->setPrix($contenu['prix']);
        $nouvProduit->setDc(false);
        $nouvProduit->setQuatitestock($contenu['quantiteStock']);
        $nouvProduit->setQuantiteminimale($contenu['quantiteMinimal']);

        // Inscrit en Bd le produit
        $em->persist($nouvProduit);
        $em->flush();
    }

    //----------------------------
    //Va chercher les produits en bd
    //----------------------------
    public function getProduits()
    {
        // Connection avec la bd 
        $em = $this->getDoctrine()->getManager();
        //Retourne les produits
        return $em->getRepository(Produits::class)->findAll();
    }

    //--------------------------
    //Modifie un produit en bd
    //--------------------------
    public function modifProduit($modifProduit)
    {
        // Connection avec la bd 
        $em = $this->getDoctrine()->getManager();
        //Va chercher le produit en bd
        $produit = $em->getRepository(Produits::class)->find($modifProduit['id']);

        // Change les info du produit
        $produit->setnomProduit($modifProduit['nomProduit']);
        $produit->setIdCategorie($modifProduit['categorie']);
        $produit->setDescription($modifProduit['description']);
        $produit->setQuatiteStock($modifProduit['quantiteStock']);
        $produit->setQuantiteminimale($modifProduit['quantiteMinimal']);
        $produit->setPrix($modifProduit['prix']);
        //Fait les changement en Bd
        $em->flush(); 
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
        return $total;  
    }

    //----------------------------------
    //Function qui retourne true si l'Admin est connecter
    //----------------------------------
    public function adminConnecter($req)
    {
        //Va chercher le session
        $session = $req->getSession();
        //Valide que la session contient la variable 
        if ($session->has('adminConnecter'))
        {
            return $session->get('adminConnecter');
        }
        else
            return false;
    }
    

}