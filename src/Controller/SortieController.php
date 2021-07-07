<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Repository\EtatRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    #[Route('/sorties', name: 'sortie_liste_sorties')]
    public function listeSorties(SortieRepository $sortieRepository,
                                 EtatRepository $etatRepository,
                                 ParticipantRepository $participantRepository): Response
    {
        //$sorties = $sortieRepository->findAll();
        $sortie = new Sortie();
        $sortie->setNom('Philo')
            ->setDateHeureDebut(new \DateTime('now'))
            ->setDateLimiteInscription(new \DateTime('now + 2 day'))
            ->setNbInscriptionsMax(8)
            ->setEtat($etatRepository->find(1))
            ->setParticipant($participantRepository->find(1));
        $sorties = [$sortie];

        return $this->render('sortie/liste-sorties.html.twig', [
            'sorties' => $sorties,
        ]);
    }
}
