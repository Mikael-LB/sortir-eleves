<?php


namespace App\BO;


use App\Entity\Campus;
use Symfony\Component\Validator\Constraints as Assert;

class Filtrer
{
    /**
     * @var Campus
     * @Assert\NotNull(message="Ne peut être vide")
     */
    private $campus;
    /**
     * @var string
     * @Assert\Length (max="50", maxMessage="Recherche limitée à 50 caractères")
     */
    private $nom;
    /**
     * @var \DateTime
     */
    private $dateHeureDebut;
    /**
     * @var \DateTime
     * @Assert\GreaterThan(propertyPath="dateHeureDebut",
     *     message="La date de fin doit être postérieure à la date de début")
     */
    private $dateHeureFin;
    /**
     * @var boolean
     */
    private $isOrganisateur;
    /**
     * @var boolean
     */
    private $isInscrit;
    /**
     * @var boolean
     */
    private $notInscrit;
    /**
     * @var boolean
     */
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