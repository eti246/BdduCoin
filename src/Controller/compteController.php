<?php
namespace App\Controller;
// Auteur : Etienne Desrochers
// Date : 2020-05-06
// But : Controle des Comptes
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Entity\Produits;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Entity\Compte;

class compteController extends AbstractController 
{
  
    /**
     * @Route("menuCreerCompte",name="menuCreerCompte")
     */
    public function menuCreerCompteAction(Request $req)
    {
        //Va chercher la session
        $session = $req->getSession();

        if($session->has('utilisateur'))
        {
            //Initialise la session
            $this->setSession($req); 
        } 
        $contenuPost = $_POST;
        
       //Verifie si l'utilisateur à rentré des information
        if($this->verificationCompte($contenuPost,$req)and  !empty($contenuPost))  
        {
            //Place le compte dans la session les informations
            $this->setSessionInfo($contenuPost,$req);
            return $this->render('menuCompteValidation.html.twig');
        } 
        else 
            //Place le compte dans la session les informations
            $this->setSessionInfo($contenuPost,$req);
            return $this->render('menuCompte.html.twig');	
    }
     /**
     * @Route("menuDeconnection",name="menuDeconnection")
     */
    public function menuDeconnectionAction(Request $req)
    {

        // Déconnecte le compte
        $this->deconnexionCompte($req);
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
     * @Route("menuConnection",name="menuConnection")
     */
    public function menuConnectionAction(Request $req)
    {
                
        //Va chercher la session
        $session = $req->getSession();

        //Connecte le compte
        $this->connexionCompte($session->get('utilisateurInfo'),$session->get('mpInfo'),$req);
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
     * @Route("menuConnectionUser",name="menuConnectionUser")
     */
    public function menuConnectionUserAction(Request $req)
    {
        // Va chercher le conteenu du post
        $contenuPost = $_POST;
        //Va chercher la session
        $session =$req->getSession();
        $session->set('Echec', true);
        //Regarde si le poste est vide 
        if(!empty($contenuPost))
        {
            // Regsrde et connecte ke compte
            if($this->connexionCompte($contenuPost['userConnection'], $contenuPost['mpConnection'],$session))
            {
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
            // Place l'echec de la connection
            $session->set('Echec', false); 
            
        }
        return $this->render('menuConnection.html.twig');
    }



      /**
     * @Route("rajouterCompte",name="rajouterCompte")
     */
    public function rajouterCompteAction(Request $req)
    {
        //Va chercher le contenu de la session
        $session =$req->getSession();
 
        //Rajoute un compte
        $this->rajouterCompte($req);

        //Connecte le compte
        $this->connexionCompteCreation($req);
        return $this->render('modifierCompte.html.twig');
    }

     /**
     * @Route("modifierCompte",name="modifierCompte")
     */
    public function modifierCompteAction(Request $req)
    {
        
       //Va chercher le contenu du poste
        $contenuPost = $_POST;
        //Regarde si l'ancien mot de passe est dans le POST
        if(isset($contenuPost['AncientMP']))
        {
            //Va chercher la session
            $session = $req->getSession();
            //Modifie le mot de passe
            if($this->modifierMotPasse($contenuPost))   
            {
                //Enlève l'erreur si elle existe
                if($session->has('InvalideMP'))
                    $session->remove('InvalideMP');
            }
            else
            {  
                //Place l'erreur
                $session->set('InvalideMP',true);

            }

        }
        //Regarde si le contenu est vide
        else if(!empty($contenuPost))
        {
            //modifie le compte
            $this->modifierCompte($contenuPost,$req);
        }
        //Affiche la page de modification
        return $this->render('modifierCompte.html.twig');
    }
    
    //---------------------
    // Verifie que tout les champs de la creation de compte sont present
    //--------------------- 
    public function verificationCompte($information,$req)
    {
        //Regarde si les information sont vide
        if(empty($information))
            return false;
        else
        {
            //Regarde si il y a un champs vide
            if($this->regardeChampVide($information) ==true)
            {
                // Valide les champs
                $utilisateur = $this->valideNomUtilisateur($information['nomUtilisateur']);
                $prenom = $this->valideLongeurChamps($information['prenom'],"Prenom");
                $nom =$this->valideLongeurChamps($information['nomUtilisateur'],"Nom");
                $adresse =$this->valideLongeurChamps($information['adresse'],'Adresse');
                $codePostal=$this->valideCodePostal($information['codePostal']);
                $telephone=$this->valideTelephone($information['telephone']);
                $courriel=$this->valideCourriel($information['courriel']);
                $motPasse=$this->valideLongeurChamps($information['mp'], 'Mot de Passe');

                //Place les informations dans la session
                $session = $req->getSession();
                $session->set('utilisateur',$utilisateur);
                $session->set('prenom',$prenom);
                $session->set('nom',$nom);
                $session->set('adresse',$adresse);
                $session->set('codePostal',$codePostal);
                $session->set('telephone',$telephone);
                $session->set('courriel',$courriel);
                $session->set('mp',$motPasse);
                // Valide le mot de passe
                if($motPasse == $information['mpConfirme']){$session->set('mpConfirme',true);}
                else {$session->set('motConfirme',"Les mots de passe ne sont pas identique");}
            
                
                //Retourne une validation du compte
                return $this->validation($session); 
            }
            else
                return false;
            
        }
   
    }
     
    //---------------------
    // Verifie que tout les champs de la creation de compte sont present
    //--------------------- 
    public function regardeChampVide($information)
    {
        // regarde si un des champs est vide et lance un alerte 
        if(empty($information['nomUtilisateur']))
        {
            $message = "Veuillez remplir le champ Nom dutilisateur";
            echo "<script type='text/javascript'>alert('$message');</script>";
            return false;
        }
        else if(empty($information['prenom']))
        {
            $message = "Veuillez remplir le champ Prenom";
            echo "<script type='text/javascript'>alert('$message');</script>";
            return false;
        }
        else if(empty($information['nom']))
        {
            $message = "Veuillez remplir le champ Nom";
            echo "<script type='text/javascript'>alert('$message');</script>";
            return false;
        }
        else if(empty($information['adresse']))
        {
            $message = "Veuillez remplir le champ adresse";
            echo "<script type='text/javascript'>alert('$message');</script>";
            return false; 
        }  
        else if(empty($information['ville']))
        {
            $message = "Veuillez remplir le champ ville";
            echo "<script type='text/javascript'>alert('$message');</script>";
            return false;
        }
        else if(empty($information['codePostal']))
        {
            $message = "Veuillez remplir le champ Code Postal";
            echo "<script type='text/javascript'>alert('$message');</script>";
            return false;
        }
        else if(empty($information['courriel']))
        {
            $message = "Veuillez remplir le champ Couriel";
            echo "<script type='text/javascript'>alert('$message');</script>";
            return false;
        }
        else if(empty($information['mp']))
        {
            $message = "Veuillez remplir le champ Mot de passe";
            echo "<script type='text/javascript'>alert('$message');</script>";
            return false;
        }     
        else if(empty($information['mpConfirme']))
        {
            $message = "Veuillez remplir le champ Confiramtion du mot de passe";
            echo "<script type='text/javascript'>alert('$message');</script>";
            return false;
        }
        //retourne true les champs sont tous remplit
        return true;
    }
    
    //---------------------
    // Valide le d'utilisateur
    //--------------------- 
    public function valideNomUtilisateur($nom)
    {
        // Connection avec la bd 
        $em = $this->getDoctrine()->getManager();
        // Va chercher les information dans la bd
        $utilisateur = $em->getRepository(Compte::class)->findAll();
        
        //Pour chaque compte
        foreach ($utilisateur as $compte)
        {
            // Regarde le compte est celui que l'on recherche
            if($nom == $compte->getNomUtilisateur())
                //retourne l'erreur
                return "Le nom d'utilisateur est déja utilisé";
        }
        // Valide la logueur du Nom d'utilisateur
        return $this->valideLongeurChamps($nom,"Nom d'utilisateur");
    }

    //---------------------
    // Valide longeur champs
    //--------------------- 
    public function valideLongeurChamps($nom, $Type)
    {
        //Valide la longeur
        if(strlen($nom)==1)
            return $Type." Trop Court";
        else if(strlen($nom) >15)
             return $Type." Trop Long";
        return true;
    }

    //---------------------
    // Valide code Postal 
    //--------------------- 
    public function valideCodePostal($code)
    {
        //https://gist.github.com/james2doyle/c310e6ceeb3bad437621#file-valid-canadian-postal-code-php-L29
        
        $pattern="/^([a-zA-Z]\d[a-zA-Z])\ {0,1}(\d[a-zA-Z]\d)$/";
        //Valide le Code Postal
        if (preg_match($pattern,$code))
            return true;
        else
            //retourne l'erreur
            return "Code Postal invalide";
    }
    //---------------------
    // Valide telephone
    //--------------------- 
    public function valideTelephone($telephone)
    {
        
        $pattern="/^(([0-9]{3})|(\([0-9]{3}\)))(\s|\-)?[0-9]{3}(\s|\-)?[0-9]{4}$/";
        //Valide le Téléphone    
        if (preg_match($pattern,$telephone))
            return true;
        else
            //Retourne l'erreur
            return "Numero de téléphone invalide";
    }

     //---------------------
    // Valide telephone
    //--------------------- 
    public function valideCourriel($courriel)
    {
        
        $pattern="/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix";
        //valide le courriel
        if (preg_match($pattern,$courriel))
            return true;
        else
            //Retourne l'erreur
            return "Courriel invalide";
        
    }
   

    //---------------------
    // Valide tout les champs
    //--------------------- 
    public function validation($session)
    {
        //Valide le compte
        return $session->get('utilisateur') ===true 
            and $session->get('prenom')===true
             and $session->get('nom')===true  
              and $session->get('adresse')===true 
               and $session->get('codePostal')===true
                and $session->get('telephone')===true
                 and $session->get('courriel')===true
                  and $session->get('mp')===true
                   and $session->get('mpConfirme')===true;
        
    }

    //---------------------
    // Place la session
    //--------------------- 
    public function setSession($req)
    {
        // Met les champs dans la session
        $session = $req->getSession();
        $session->set('utilisateur',false);
        $session->set('prenom',false);
        $session->set('nom',false);
        $session->set('adresse',false);
        $session->set('ville',false);
        $session->set('codePostal',false);
        $session->set('telephone',false);
        $session->set('courriel',false);      
        $session->set('mpConfirme',true);
    }

    //---------------------
    // Place les information du compte dans la session
    //--------------------- 
    public function setSessionInfo($info,$req)
    {
      //Va chercher la session
        $session = $req->getSession();
        
        //Regarde si la session contient l'utilisateur
        if($session->has('utilisateur') and !empty($info))
        {   
            //Place les information de l'utilisateur dans la session
            $session->set('utilisateurInfo',$info['nomUtilisateur']);
            $session->set('prenomInfo',$info['prenom']);
            $session->set('nomInfo',$info['nom']);
            $session->set('adresseInfo',$info['adresse']);
            $session->set('genreInfo',$info['genre']);
            $session->set('villeInfo',$info['ville']);
            $session->set('provinceInfo',$info['province']);
            $session->set('codePostalInfo',$info['codePostal']);
            $session->set('telephoneInfo',$info['telephone']);
            $session->set('courrielInfo',$info['courriel']);  
            $session->set('mpInfo',$info['mp']);    
        }
        else
        {
            //Place les champs vide dans la session
            $session->set('utilisateurInfo','');
            $session->set('prenomInfo','');
            $session->set('nomInfo','');
            $session->set('adresseInfo','');
            $session->set('genreInfo','');
            $session->set('villeInfo','');
            $session->set('provinceInfo','');
            $session->set('codePostalInfo','');
            $session->set('telephoneInfo','');
            $session->set('courrielInfo','');  
            $session->set('mpInfo',''); 
        }
    }
    //---------------------
    // Rajoute un compte dans la BD
    //--------------------- 
    public function rajouterCompte($req)
    {

        //Va chercher la session
        $session = $req->getSession();
        // Créé un nouveau compte
        $nouvCompte = new Compte;
        
        // donne au nouveau compte ses information
        $nouvCompte->setNomUtilisateur($session->get('utilisateurInfo'));;
        $nouvCompte->setPrenom($session->get('prenomInfo'));
        $nouvCompte->setNom($session->get('nomInfo'));
        $nouvCompte->setGenre($session->get('genreInfo'));
        $nouvCompte->setAdresse($session->get('adresseInfo'));
        $nouvCompte->setProvince($session->get('provinceInfo'));
        $nouvCompte->setVille($session->get('villeInfo'));
        $nouvCompte->setCodePostal($session->get('codePostalInfo'));
        $nouvCompte->setTelephone($session->get('telephoneInfo'));
        $nouvCompte->setCourriel($session->get('courrielInfo'));
        $nouvCompte->setMotPasse($this->encryptMp( $session->get('mpInfo')));
       
        // Place le nouveau compte dans la BD
        $doc = $this->getDoctrine();
        $em = $doc->getManager();
        
        $em->persist($nouvCompte);
        $em->flush();
    }
    //---------------------
    // Connecte le compte de l'utilisateur
    //--------------------- 
    public function connexionCompte($user,$mp,$session)
    {
        //Regarder si l'utilisateur existe
        if($this->checkUser($user))
        {
            //Regarder si le mot de passe est celui de l'utilisateur
            if($this->checkMp($user,$mp))
            {
                // Connection avec la bd 
                $em = $this->getDoctrine()->getManager();
                // Va chercher les information dans la bd
                $utilisateur = $em->getRepository(Compte::class)->findAll(); 
                //Pour chaque compte  
                foreach ($utilisateur as $compte)
                {    
                    // Regarde si le compte eest celui que l'on recherche
                    if($user == $compte->getNomUtilisateur())
                    {
                        // Connecte le compte
                        $this->connectionSetInfo($user,$session);
                        return true;
                    }
                }
            }
            else
                return false;
        }
        else
            return false;



    }

    //-------------------        
    // Place les information dans la session lors d'une connexion
    //-------------------        
    
    public function connectionSetInfo($user,$session)
    {
        // Connection avec la bd 
        $em = $this->getDoctrine()->getManager();
        // Va chercher les information dans la bd
        $utilisateur = $em->getRepository(Compte::class)->findAll();
        
        //Pour chaque compte
        foreach ($utilisateur as $compte)
        {
            //Regarde si le compte est celui que l'on recherche
            if($user == $compte->getNomUtilisateur())
            {
                $connection = $compte;
                break;
            }        
         }

        // Place dans la session les informations
        $session->set('Connecter',true);
        $session->set('NomUtilisateurConnecter',$connection->getNomUtilisateur());
        $session->set('PrenomConnecter',$connection->getPrenom());
        $session->set('NomConnecter',$connection->getNom());
        $session->set('AdresseConnecter',$connection->getAdresse());
        $session->set('GenreConnecter',$connection->getGenre());
        $session->set('VilleConnecter',$connection->getVille());
        $session->set('ProvinceConnecter',$connection->getProvince());
        $session->set('CodePostalConnecter',$connection->getCodePostal());
        $session->set('TelephoneConnecter',$connection->getTelephone());
        $session->set('CourrielConnecter',$connection->getCourriel());  
        $session->set('MPConnecter',$connection->getMotPasse());
    }
    //-------------------        
    // Valide l'utilisateur
    //-------------------        
    public function checkUser($user)
    {
        // Connection avec la bd 
        $em = $this->getDoctrine()->getManager();
        // Va chercher les information dans la bd
        $utilisateur = $em->getRepository(Compte::class)->findAll();
        
        //Pour chaque Compte
        foreach ($utilisateur as $compte)
        {
            // Regarde si on a atteint le compte rechercher
            if($user == $compte->getNomUtilisateur())
            {
                return true;
            }
        }
        return false;
        
    }
    //-------------------        
    // Valide le mot de passe
    //-------------------        
    public function checkMp($user,$mp)
    {
            
         // Connection avec la bd 
         $em = $this->getDoctrine()->getManager();
         // Va chercher les information dans la bd
         $utilisateur = $em->getRepository(Compte::class)->findAll();
         //Pour chaque Compte
         foreach ($utilisateur as $compte)
         {
            // Regarde si on atteint le bon compte
             if($user == $compte->getNomUtilisateur())
                // retourne le mot de passe
                 return (password_verify($mp,$compte->getMotPasse()));
         }
         return false;
    }

    //-------------------        
    // Connecte le compte suite à sa création
    //-------------------            
    public function connexionCompteCreation($req)
    {
        //Va chercher la session
        $session = $req->getSession();
        
        //Place les informations dans la sessions 
        $session->set('Connecter',true);
        $session->set('NomUtilisateurConnecter', $session->get('utilisateurInfo'));
        $session->set('PrenomConnecter',$session->get('prenomInfo'));
        $session->set('NomConnecter',$session->get('nomInfo'));
        $session->set('AdresseConnecter',$session->get('adresseInfo'));
        $session->set('GenreConnecter',$session->get('genreInfo'));
        $session->set('VilleConnecter',$session->get('villeInfo'));
        $session->set('ProvinceConnecter',$session->get('provinceInfo'));
        $session->set('CodePostalConnecter',$session->get('codePostalInfo'));
        $session->set('TelephoneConnecter',$session->get('telephoneInfo'));
        $session->set('CourrielConnecter',$session->get('courrielInfo'));  
        $session->set('MPConnecter',$session->get('mpInfo'));
        
    }
    //-------------------        
    // Deconnecte le compte
    //-------------------            
    public function deconnexionCompte($req)
    {
        // Va chercher la session
        $session = $req->getSession();

        // Deconnecte l'utilisateur
        $session->set('Connecter',false);
        $session->set('NomUtilisateurConnecter','');
        $session->set('PrenomConnecter','');
        $session->set('NomConnecter','');
        $session->set('AdresseConnecter','');
        $session->set('GenreConnecter','');
        $session->set('VilleConnecter','');
        $session->set('ProvinceConnecter','');
        $session->set('CodePostalConnecter','');
        $session->set('TelephoneConnecter','');
        $session->set('CourrielConnecter','');  
        $session->set('MPConnecter','');
    }
    
    //-------------------        
    // Modifie le compte
    //-------------------        
    public function modifierCompte($donner, $req)
    {
        // Connecte à la BD
        $em = $this->getDoctrine()->getManager();
        $compte = $em->getRepository(Compte::class)->findAll();
        
        //Pour chaque compte
        foreach ($compte as $user )
        {
            // Regarde si on atteint le bon compte
            if($user->getNomUtilisateur() == $donner['modifNomUtilisateur'])
            {
                // Va chercher le contenu de la session
                $session = $req->getSession();

                //Réinitialise les erreurs
                $this->resetInvalide($session);
               
                // Valide le prenom
                if($this->valideLongeurChamps($donner['modifPrenom'],"Prenom") ===true)
                {
                    // Change le prenom
                    $user->setPrenom($donner['modifPrenom']);
                    // Change le prenom dans la session
                    $session->set('PrenomConnecter',$donner['modifPrenom']);
                    if($session->has('InvaldiePrenom'))
                        // Enlève erreur
                        $session->remove('InvalidePrenom');
                }
                else
                    //Place l'erreur
                    $session->set('InvalidePrenom',true);

                //Valide le nom    
                if($this->valideLongeurChamps($donner['modifNom'],"Nom") ===true)
                {
                    //Change le nom
                    $user->setNom($donner['modifNom']);
                    // Change le nom dans la session
                    $session->set('NomConnecter',$donner['modifNom']);
                    if($session->has('InvaldieNom'))
                        //Enlève l'erreur
                        $session->remove('InvalideNom');
                }
                else
                    //Place l'erreur
                    $session->set('InvalideNom',true);

                // Valide l'adresse
                if($this->valideLongeurChamps($donner['modifAdresse'],"Adresse") ===true)
                {
                    //Change l'adresse
                    $user->setAdresse($donner['modifAdresse']);
                    //Change l'adresse dans la session
                    $session->set('AdresseConnecter',$donner['modifAdresse']);
                    if($session->has('InvaldieAdresse'))
                        //Enlève l'erreur
                        $session->remove('InvalideAdresse');
                }
                else
                    //Place l'erreur
                    $session->set('InvalideAdresse',true);

                //Place le genre    
                $user->setGenre($donner['modifGenre']);
                $session->set('GenreConnecter',$donner['modifGenre']);

                // Valdie la ville
                if($this->valideLongeurChamps($donner['modifVille'],"") ===true)
                {
                    //Change la ville
                    $user->setVille($donner['modifVille']);
                    //Change la ville dans la session
                    $session->set('VilleConnecter',$donner['modifVille']);
                    if($session->has('InvaldieVille'))
                        //enlève l'erreur
                        $session->remove('InvalideVille');
                }
                else
                    //Place l'erreur
                    $session->set('InvalideVille',true);

                //Place la province 
                $user->setProvince($donner['modifProvince']);
                $session->set('ProvinceConnecter',$donner['modifProvince']);

                // Valide la code Postal
                if($this->valideCodePostal($donner['modifCodePostal']) ===true)
                {
                    // Change le code postal
                    $user->setCodePostal($donner['modifCodePostal']);
                    // Change le code postal dans la session
                    $session->set('CodePostalConnecter',$donner['modifCodePostal']);
                    if($session->has('InvaldieCodePostal'))
                        //Enlève l'erreur
                        $session->remove('InvalideCodePostal');
                }
                else
                    //Place l'erreur
                    $session->set('InvalideCodePostal',true);

                // Valide le courriel
                if($this->valideCourriel($donner['modifCourriel']) ===true)
                {
                    // Change le Courriel
                    $user->setCourriel($donner['modifCourriel']);
                    // Change le Courriel dans la session
                    $session->set('CourrielConnecter',$donner['modifCourriel']);
             
                    if($session->has('InvaldieCourriel'))
                        // Enlève l'Erreur
                        $session->remove('InvalideCourriel');
                }
                else
                    //Place l'erreur
                    $session->set('InvalideCourriel',true);

                //Valide le téléphone
                if($this->valideTelephone($donner['modifTelephone']) ===true)
                {
                    //Change le Telephone
                    $user->setTelephone($donner['modifTelephone']);
                    $session->set('TelephoneConnecter',$donner['modifTelephone']);
                    if($session->has('InvaldieTelephone'))
                    //Enlève l'Erreur
                        $session->remove('InvalideTelephone');
                }
                else
                    //Place l'erreurs
                    $session->set('InvalideTelephone',true);

                //Met a jour la bd
                $em->flush();


            }

        }
    }

    //------------
    // Réinitialise les erreur
    //------------
    public function resetInvalide($session)
    {
        //Place les erreurs
        $session->set('InvalidePrenom',false);
        $session->set('InvalideNom',false);
        $session->set('InvalideAdresse',false);
        $session->set('InvalideVille',false);
        $session->set('InvalideCodePostal',false);
        $session->set('InvalideCourriel',false);
        $session->set('InvalideTelephone',false);
    }
    //------------------------------------
    // Fonction qui retourne les catégorie
    //------------------------------------
    public function getCategorie()
    {
        // Connection à la BD
        $connexion=$this->getDoctrine()->getManager()->getConnection();
        // La requette
        $req="SELECT * FROM `catagorie`";
        $reqBD=$connexion->prepare($req);
        // Execute la requette
        $reqBD->execute();
        // Prend les Categories
        $res=$reqBD->fetchAll();
        // Retourne les categories
        return $res;
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


    //------------------
    // Modifie le mot de passe
    //------------------
    public function modifierMotPasse($info)
    {
        //Connecte à la bd
        $em = $this->getDoctrine()->getManager();
        //Va chercher les compte
        $compte = $em->getRepository(Compte::class)->findAll();

        //Pour chaque compte
        foreach ($compte as $user )
        {
            // valide l'ancien mp
            if($info['AncientMP'] == $user->getMotPasse() and $info['modifNomUtilisateur'] == $user->getNomUtilisateur('nomUtilisateur'));
            {
                //valide le nouveau mp 
                if($info['NouvMP'] == $info['ConfirmMP'])
                {
                    //Change le mot de passe
                    $user->setMotPasse($info['NouvMP']);
                    $em->flush();
                    return false;
                } 
            }
            
        }
        return true;
    }


    //-------------------------------------------
    //Encryption du mot de passe
    //-------------------------------------------
    public function encryptMp($mp)
    {
        return password_hash($mp, PASSWORD_DEFAULT);
    }
   
}
?>