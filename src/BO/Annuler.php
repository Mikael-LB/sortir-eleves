<?php


namespace App\BO;


use App\Entity\Campus;
use Symfony\Component\Validator\Constraints as Assert;

class Annuler
{
    /**
     * @var string
     * @Assert\NotNull(message="Ne peut être vide")
     */
    private $motif;

    /**
     * @return string
     */
    public function getMotif(): string
    {
        return $this->motif;
    }

    /**
     * @param string $motif
     */
    public function setMotif(string $motif): void
    {
        $this->motif = $motif;
    }

}