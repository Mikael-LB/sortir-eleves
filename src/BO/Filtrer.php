<?php


namespace App\BO;


use App\Entity\Campus;

class Filtrer
{
    private $campus;
    private $nom;
    private $dateHeureDebut;
    private $dateHeureFin;
    private boolean $isOrganisateur;
    private boolean $isInscrit;
    private boolean $notInscrit;
    private boolean $oldSorties;

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
     * @return bool
     */
    public function isOrganisateur(): bool
    {
        return $this->isOrganisateur;
    }

    /**
     * @param bool $isOrganisateur
     */
    public function setIsOrganisateur(bool $isOrganisateur): void
    {
        $this->isOrganisateur = $isOrganisateur;
    }

    /**
     * @return bool
     */
    public function isInscrit(): bool
    {
        return $this->isInscrit;
    }

    /**
     * @param bool $isInscrit
     */
    public function setIsInscrit(bool $isInscrit): void
    {
        $this->isInscrit = $isInscrit;
    }

    /**
     * @return bool
     */
    public function isNotInscrit(): bool
    {
        return $this->notInscrit;
    }

    /**
     * @param bool $notInscrit
     */
    public function setNotInscrit(bool $notInscrit): void
    {
        $this->notInscrit = $notInscrit;
    }

    /**
     * @return bool
     */
    public function isOldSorties(): bool
    {
        return $this->oldSorties;
    }

    /**
     * @param bool $oldSorties
     */
    public function setOldSorties(bool $oldSorties): void
    {
        $this->oldSorties = $oldSorties;
    }
}