<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Participant;
use App\BO\Filtrer;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\FiltrerType;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    #[Route('/sorties', name: 'sortie_liste_sorties')]
    public function listeSorties(Request $request,
                                 SortieRepository $sortieRepository,
                                 EtatRepository $etatRepository,
                                 ParticipantRepository $participantRepository): Response
    {

        //$sorties = $sortieRepository->findAll();
        /*$sortie = new Sortie();
        $sortie->setNom('Philo')
            ->setDateHeureDebut(new \DateTime('now'))
            ->setDateLimiteInscription(new \DateTime('now + 2 day'))
            ->setNbInscriptionsMax(8)
            ->setEtat($etatRepository->find(1))
            ->setParticipant($participantRepository->find(1));
        $sorties = [$sortie];*/
        $sorties = $sortieRepository->findAll();



        $filtrer = new Filtrer();
        $filtrerForm = $this->createForm(FiltrerType::class,$filtrer);
        $filtrerForm->handleRequest($request);

        if($filtrerForm->isSubmitted() && $filtrerForm->isValid()){
            $filtrer = $filtrerForm->getData();
            $sorties = $sortieRepository->findForFilterForm($filtrer);
        }

        return $this->render('sortie/liste-sorties.html.twig', [
            'filtrerForm'=>$filtrerForm->createView(),
            'sorties' => $sorties,
        ]);
    }


    #[Route('/sorties/consulter/{id}', name: 'sorties_consulter')]
    public function consulter($id,
                              SortieRepository $sortieRepository,
                                LieuRepository $lieuRepository,
                                VilleRepository $villeRepository,
                                ParticipantRepository $participantRepository): Response
    {

        //Afficher les détails concernant une sortie
        $sortie = $sortieRepository->find($id);

        $lieu = new Lieu();
        $lieu->setNom('RennesCentre')
            ->setRue('Albert')
            ->setLatitude(4)
            ->setLongitude(5);
        $lieu = $lieuRepository->find($id);

        $ville = new Ville();
        $ville->setCodePostal(35000);

        $participant = new Participant();
        $participant->setNom('Stasia')
                    ->setPseudo('st');
        $participants = [$participant];
       // $participant = $participantRepository->find($id);



        return $this->render('consulter/consulter-sorties.html.twig', [
            'sortie' => $sortie,
            'lieu' => $lieu,
            'ville'=> $ville,
            'participants'=> $participants
        ]);
    }

    #[Route('/sorties/creer', name: 'sortie_creer')]
    public function creerSortie(){


    }

}
