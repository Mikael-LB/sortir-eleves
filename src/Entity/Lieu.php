<?php

namespace App\Entity;

use App\Repository\LieuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=LieuRepository::class)
 */
class Lieu
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups ({"group_ville", "group_lieu"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length (min="4", minMessage="4 caractère minimum pour votre nom de lieu", max="255", maxMessage="Pas plus de 255 caractères SVP")
     * @Assert\Type(type="string", message="le nom du lieu doit etre une string")
     * @Groups ({"group_ville", "group_lieu"})
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length (min="4", minMessage="4 caractère minimum pour votre rue", max="255", maxMessage="Pas plus de 255 caractères SVP")
     * @Assert\Type(type="string", message="la rue doit etre une string")
     * @Groups ({"group_ville", "group_lieu"})
     */
    private $rue;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Assert\Type (type="float", message="la latitude oit etre un float exemple : 49.38816 ou -127.70166")
     * @Groups ({"group_ville", "group_lieu"})
     */
    private $latitude;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Assert\Type (type="float", message="la longitude oit etre un float exemple : 49.38816 ou -127.70166")
     * @Groups ({"group_ville", "group_lieu"})
     */
    private $longitude;

    /**
     * @ORM\OneToMany(targetEntity=Sortie::class, mappedBy="lieu")
     */
    private $sorties;

    /**
     * @ORM\ManyToOne(targetEntity=Ville::class, inversedBy="lieux")
     * @ORM\JoinColumn(nullable=false)
     * @Groups ({"group_lieu"})
     */
    private $ville;

    public function __construct()
    {
        $this->sorties = new ArrayCollection();
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

    public function getRue(): ?string
    {
        return $this->rue;
    }

    public function setRue(string $rue): self
    {
        $this->rue = $rue;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): self
    {
        $this->longitude = $longitude;

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
            $sorty->setLieu($this);
        }

        return $this;
    }

    public function removeSorty(Sortie $sorty): self
    {
        if ($this->sorties->removeElement($sorty)) {
            // set the owning side to null (unless already changed)
            if ($sorty->getLieu() === $this) {
                $sorty->setLieu(null);
            }
        }

        return $this;
    }

    public function getVille(): ?Ville
    {
        return $this->ville;
    }

    public function setVille(?Ville $ville): self
    {
        $this->ville = $ville;

        return $this;
    }
}
