<?php


namespace App\DataFixtures\Utils;


use App\Entity\AssosPartiSort;

class FixturesService
{
    public function addParticipantIfNotAlreadyInscrit($participant, $sortie, $manager):bool{
        $isAlreadyInscrit = false;
        foreach ($sortie->getAssosPartiSort() as $assos){
            if ($assos->getParticipant() === $participant){
                $isAlreadyInscrit = true;
            }
        }
        if (!$isAlreadyInscrit){
            $assosPartiSort = new AssosPartiSort();
            $assosPartiSort->setSortie($sortie)
                ->setParticipant($participant)
            ;
            $sortie->addAssosPartiSort($assosPartiSort);
            $participant->addAssosPartiSort($assosPartiSort);
            $manager->persist($participant);
            $manager->persist($assosPartiSort);
            return true;
        }else{
            return false;
        }
    }
}