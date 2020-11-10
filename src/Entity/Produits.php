<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Produits
 *
 * @ORM\Table(name="produits")
 * @ORM\Entity
 */
class Produits
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
     * @var string|null
     *
     * @ORM\Column(name="nomProduit", type="string", length=50, nullable=true)
     */
    private $nomproduit;

    /**
     * @var int|null
     *
     * @ORM\Column(name="idCategorie", type="integer", nullable=true)
     */
    private $idcategorie;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="string", length=500, nullable=true)
     */
    private $description;

    /**
     * @var int|null
     *
     * @ORM\Column(name="quatiteStock", type="integer", nullable=true)
     */
    private $quatitestock;

    /**
     * @var int|null
     *
     * @ORM\Column(name="quantiteminimale", type="integer", nullable=true)
     */
    private $quantiteminimale;

    /**
     * @var bool
     *
     * @ORM\Column(name="DC", type="boolean", nullable=false)
     */
    private $dc;

    /**
     * @var int
     *
     * @ORM\Column(name="prix", type="integer", nullable=false)
     */
    private $prix;

    public function getId(): ?int
    {
        return $this->id;
    }
    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getNomproduit(): ?string
    {
        return $this->nomproduit;
    }

    public function setNomproduit(?string $nomproduit): self
    {
        $this->nomproduit = $nomproduit;

        return $this;
    }

    public function getIdcategorie(): ?int
    {
        return $this->idcategorie;
    }

    public function setIdcategorie(?int $idcategorie): self
    {
        $this->idcategorie = $idcategorie;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getQuatitestock(): ?int
    {
        return $this->quatitestock;
    }

    public function setQuatitestock(?int $quatitestock): self
    {
        $this->quatitestock = $quatitestock;

        return $this;
    }

    public function getQuantiteminimale(): ?int
    {
        return $this->quantiteminimale;
    }

    public function setQuantiteminimale(?int $quantiteminimale): self
    {
        $this->quantiteminimale = $quantiteminimale;

        return $this;
    }

    public function getDc(): ?bool
    {
        return $this->dc;
    }

    public function setDc(bool $dc): self
    {
        $this->dc = $dc;

        return $this;
    }

    public function getPrix(): ?int
    {
        return $this->prix;
    }

    public function setPrix(int $prix): self
    {
        $this->prix = $prix;

        return $this;
    }


}
