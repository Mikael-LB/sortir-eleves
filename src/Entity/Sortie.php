<?php

namespace App\Entity;

use App\Repository\SortieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=SortieRepository::class)
 */
class Sortie
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min=3, minMessage="Plus de 3 caractères svp", max=255, maxMessage="moins de 255 caractères svp")
     * @Assert\Type(type="string", message="Le nom doit être une string")
     */
    private $nom;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\Type(type="datetime", message="La date doit être une date en format datetime")
     */
    private $dateHeureDebut;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Type(type="integer", message="La durée en minutes doit etre un entier")
     */
    private $duree;

    /**
     * @ORM\Column(type="date")
     * @Assert\Type(type="datetime", message="La date doit être une date en format date")
     * @Assert\LessThan(propertyPath="dateHeureDebut", message="La date de fermeture des inscriptions doit etre inferieure à celle de début de sortie")
     */
    private $dateLimiteInscription;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Type(type="integer", message="Le nombre de participant doit etre un entier")
     */
    private $nbInscriptionsMax;

    /**
     * @ORM\Column(type="text")
     * @Assert\Length(min=3, minMessage="Plus de 3 caractères SVP")
     * @Assert\Type (type="string", message="les infos doivent etre du texte")
     * @Assert\NotBlank()
     */
    private $infosSortie;

    /**
     * @ORM\ManyToOne(targetEntity=Participant::class, inversedBy="sorties")
     * @ORM\JoinColumn(nullable=false)
     */
    private $organisateur;

    /**
     * @ORM\ManyToOne(targetEntity=Campus::class, inversedBy="sorties")
     * @ORM\JoinColumn(nullable=false)
     */
    private $campus;

    /**
     * @ORM\ManyToOne(targetEntity=Etat::class, inversedBy="sorties")
     * @ORM\JoinColumn(nullable=false)
     */
    private $etat;

    /**
     * @ORM\ManyToOne(targetEntity=Lieu::class, inversedBy="sorties")
     * @ORM\JoinColumn(nullable=false)
     */
    private $lieu;

    /**
     * @ORM\OneToMany(targetEntity=AssosPartiSort::class, mappedBy="sortie", cascade={"remove","persist"})
     */
    private $assosPartiSort;

    public function __construct()
    {
        $this->assosPartiSort = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): self
    {
        $this->duree = $duree;

        return $this;
    }

    public function getDateLimiteInscription(): ?\DateTimeInterface
    {
        return $this->dateLimiteInscription;
    }

    public function setDateLimiteInscription(\DateTimeInterface $dateLimiteInscription): self
    {
        $this->dateLimiteInscription = $dateLimiteInscription;

        return $this;
    }

    public function getNbInscriptionsMax(): ?int
    {
        return $this->nbInscriptionsMax;
    }

    public function setNbInscriptionsMax(int $nbInscriptionsMax): self
    {
        $this->nbInscriptionsMax = $nbInscriptionsMax;

        return $this;
    }

    public function getInfosSortie(): ?string
    {
        return $this->infosSortie;
    }

    public function setInfosSortie(string $infosSortie): self
    {
        $this->infosSortie = $infosSortie;

        return $this;
    }

    public function getOrganisateur(): ?Participant
    {
        return $this->organisateur;
    }

    public function setOrganisateur(?Participant $organisateur): self
    {
        $this->organisateur = $organisateur;

        return $this;
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

    public function getEtat(): ?Etat
    {
        return $this->etat;
    }

    public function setEtat(?Etat $etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    public function getLieu(): ?Lieu
    {
        return $this->lieu;
    }

    public function setLieu(?Lieu $lieu): self
    {
        $this->lieu = $lieu;

        return $this;
    }

    /**
     * @return Collection|AssosPartiSort[]
     */
    public function getAssosPartiSort(): Collection
    {
        return $this->assosPartiSort;
    }

    public function addAssosPartiSort(AssosPartiSort $assosPartiSort): self
    {
        if (!$this->assosPartiSort->contains($assosPartiSort)) {
            $this->assosPartiSort[] = $assosPartiSort;
            $assosPartiSort->setSortie($this);
        }

        return $this;
    }

    public function removeAssosPartiSort(AssosPartiSort $assosPartiSort): self
    {
        if ($this->assosPartiSort->removeElement($assosPartiSort)) {
            // set the owning side to null (unless already changed)
            if ($assosPartiSort->getSortie() === $this) {
                $assosPartiSort->setSortie(null);
            }
        }

        return $this;
    }
}
