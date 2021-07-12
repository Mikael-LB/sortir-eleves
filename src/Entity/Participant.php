<?php

namespace App\Entity;

use App\Repository\ParticipantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ParticipantRepository::class)
 */
class Participant implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\Length (min=3, minMessage="3 caractère minimum pour votre prénom", max="50", maxMessage="Pas plus de 50 caractères SVP")
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\Length (min="3", minMessage="3 caractère minimum pour votre prénom", max="50", maxMessage="Pas plus de 50 caractères SVP")
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=25)
     * @Assert\Length(min="10", minMessage="Votre numéro doit avoir 10 chiffres", max="25", maxMessage="Pas plus de 10 chiffres (espace/point et +33 non inclus)")
     */
    private $telephone;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Assert\Length (min="4", minMessage="4 caractère minimum pour votre pseudo", max="50", maxMessage="Pas plus de 50 caractères SVP")
     */
    private $pseudo;

    /**
     * @ORM\Column(type="boolean")
     */
    private $estAdministrateur;

    /**
     * @ORM\Column(type="boolean")
     */
    private $compteActif;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $urlImage;

    /**
     * @ORM\OneToMany(targetEntity=Sortie::class, mappedBy="organisateur")
     */
    private $sorties;

    /**
     * @ORM\ManyToOne(targetEntity=Campus::class, inversedBy="participants")
     * @ORM\JoinColumn(nullable=false)
     */
    private $campus;

    /**
     * @ORM\OneToMany(targetEntity=AssosPartiSort::class, mappedBy="participant")
     */
    private $assosPartiSort;

    public function __construct()
    {
        $this->sorties = new ArrayCollection();
        $this->assosPartiSort = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(?string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getEstAdministrateur(): ?bool
    {
        return $this->estAdministrateur;
    }

    public function setEstAdministrateur(bool $estAdministrateur): self
    {
        $this->estAdministrateur = $estAdministrateur;

        return $this;
    }

    public function getCompteActif(): ?bool
    {
        return $this->compteActif;
    }

    public function setCompteActif(bool $compteActif): self
    {
        $this->compteActif = $compteActif;

        return $this;
    }

    public function getUrlImage(): ?string
    {
        return $this->urlImage;
    }

    public function setUrlImage(?string $urlImage): self
    {
        $this->urlImage = $urlImage;

        return $this;
    }

    /**
     * @return Collection|Sortie[]
     */
    public function getSorties(): Collection
    {
        return $this->sorties;
    }

    public function addSorty(Sortie $sorty): self
    {
        if (!$this->sorties->contains($sorty)) {
            $this->sorties[] = $sorty;
            $sorty->setOrganisateur($this);
        }

        return $this;
    }

    public function removeSorty(Sortie $sorty): self
    {
        if ($this->sorties->removeElement($sorty)) {
            // set the owning side to null (unless already changed)
            if ($sorty->getOrganisateur() === $this) {
                $sorty->setOrganisateur(null);
            }
        }

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
            $assosPartiSort->setParticipant($this);
        }

        return $this;
    }

    public function removeAssosPartiSort(AssosPartiSort $assosPartiSort): self
    {
        if ($this->assosPartiSort->removeElement($assosPartiSort)) {
            // set the owning side to null (unless already changed)
            if ($assosPartiSort->getParticipant() === $this) {
                $assosPartiSort->setParticipant(null);
            }
        }

        return $this;
    }
}
