<?php


namespace App\BO;


use App\Entity\Campus;

class Filtrer
{
    private $campus;
    private $nom;
    private $dateHeureDebut;
    private $dateHeureFin;
    private $isOrganisateur;
    private $isInscrit;
    private $notInscrit;
    private $oldSorties;

    public function __construct(){

    }

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): self
    {
        $this->campus = $campus;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDateHeureDebut(): ?\DateTimeInterface
    {
        return $this->dateHeureDebut;
    }

    public function setDateHeureDebut(\DateTimeInterface $dateHeureDebut): self
    {
        $this->dateHeureDebut = $dateHeureDebut;

        return $this;
    }

    public function getDateHeureFin(): ?\DateTimeInterface
    {
        return $this->dateHeureFin;
    }

    public function setDateHeureFin(\DateTimeInterface $dateHeureFin): self
    {
        $this->dateHeureFin = $dateHeureFin;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsOrganisateur()
    {
        return $this->isOrganisateur;
    }

    /**
     * @param mixed $isOrganisateur
     */
    public function setIsOrganisateur($isOrganisateur): void
    {
        $this->isOrganisateur = $isOrganisateur;
    }

    /**
     * @return mixed
     */
    public function getIsInscrit()
    {
        return $this->isInscrit;
    }

    /**
     * @param mixed $isInscrit
     */
    public function setIsInscrit($isInscrit): void
    {
        $this->isInscrit = $isInscrit;
    }

    /**
     * @return mixed
     */
    public function getNotInscrit()
    {
        return $this->notInscrit;
    }

    /**
     * @param mixed $notInscrit
     */
    public function setNotInscrit($notInscrit): void
    {
        $this->notInscrit = $notInscrit;
    }

    /**
     * @return mixed
     */
    public function getOldSorties()
    {
        return $this->oldSorties;
    }

    /**
     * @param mixed $oldSorties
     */
    public function setOldSorties($oldSorties): void
    {
        $this->oldSorties = $oldSorties;
    }


}