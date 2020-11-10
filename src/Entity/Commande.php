<?php
namespace App\Entity;

use App\Entity\CommandeDetail;
use Doctrine\ORM\Mapping as ORM;

/**
 * Commande
 *
 * @ORM\Table(name="commande", indexes={@ORM\Index(name="IDX_6EEAA67D19EB6921", columns={"client_id"})})
 * @ORM\Entity
 */
class Commande
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_commande", type="datetime", nullable=false)
     */
    private $dateCommande;

    /**
     * @var \Compte
     *
     * @ORM\ManyToOne(targetEntity="Compte")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="client_id", referencedColumnName="id")
     * })
     */
    private $client;

    public $total;
    public $temp;
    public $tempValeur;

    public function setTemp($temp)
    {
        /*$EpochDateCommande = $this->getDate()->getTimeStamp();
        
        $EpochPresent = $DateActuelle->getTimeStamp();
        $NbSecondeEntreDates = $EpochPresent – $EpochDateCommande; */

        // 
        $DateActuelle = new \DateTime();
        $EpochDateCommande = $this->getDateCommande()->getTimeStamp();
       $EpochPresent = $DateActuelle->getTimeStamp();

        
        $this->temp =$EpochPresent-$EpochDateCommande <172800;
        
        $this->tempValeur =($EpochDateCommande+172800)-($EpochPresent-$EpochDateCommande);
        
        return $this;
    }

    public function getTemp()
    {
        $DateActuelle = new \DateTime();
        $EpochDateCommande = $this->getDateCommande()->getTimeStamp();
       $EpochPresent = $DateActuelle->getTimeStamp();
        return ($EpochDateCommande+172800)-$EpochDateCommande;
    }
    public function setTotal($total)
    {
        $this->total= $total;

        return $this;
    }

    public function getTotal()
    {
        return $this->$total;
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateCommande(): ?\DateTimeInterface
    {
        return $this->dateCommande;
    }

    public function setDateCommande(\DateTimeInterface $dateCommande): self
    {
        $this->dateCommande = $dateCommande;

        return $this;
    }

    public function getClient(): ?Compte
    {
        return $this->client;
    }

    public function setClient(?Compte $client): self
    {
        $this->client = $client;

        return $this;
    }





    // -----------------------
    //Function qui retourne le total brute de la commande
    // -----------------------
    public function getCoutCommandeBrute()
    {
        /*//Connecte à la bd
        $em =  Controller::getDoctrine()->getManager();
        //Va chercehr tout les details de commandes
        $detailAll = $em->getRepository(CommandeDetail::class)->findAll();
        //Va contenir le total brute
        $totalBrute = 0;
        //Pour chaque Detail
        foreach($detailAll as $detail)
        {
            // Valide que le detail fait partit de la commande
            if($detail->getCommande()->getId() == $idCommande)
                //Rajoute le prix à la commande 
                $totalBrute += $detail->getProduit()->getPrix() * ($detail->getQuantite() + $detail->getQuantiteRupture);   
        }
        
        // Retourne le TotalBrute*/
        return 0;
    }
}
