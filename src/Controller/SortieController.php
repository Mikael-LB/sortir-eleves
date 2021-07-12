<?php

namespace App\Controller;

use App\Entity\AssosPartiSort;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\BO\Filtrer;
use App\Entity\Sortie;
use App\Entity\Campus;
use App\Entity\Ville;
use App\Form\FiltrerType;
use App\Form\SortieType;
use App\Repository\CampusRepository;
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
            ->setOrganisateur($participantRepository->find(1));
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
    public function creerSortie(SortieRepository $sortieRepository, CampusRepository $campusRepository, EtatRepository $etatRepository ,EntityManagerInterface $entityManager,Request $request): Response
    {
        $sortie = new Sortie();
        $organisateur = $this->getUser();
        /**
         * @var $organisateur Participant
         */
        $sortie->setOrganisateur($organisateur);
//        $campus = $campusRepository->find($organisateur->getCampus());
        $campusId = $organisateur->getCampus()->getId();
        $campus = $campusRepository->find($campusId);
        $sortie->setCampus($organisateur->getCampus());
        $sortie->setEtat($etatRepository->findOneByLibelle(['En Création']));

        $sortieForm = $this->createForm(SortieType::class,$sortie);

        $sortieForm->handleRequest($request);
            $dataLieu = $sortieForm->get('Lieu')->getData();

            $sortie->setLieu($dataLieu);
            $dataVille = $sortieForm->get('Ville')->getData();


//        dd($sortie);
//        dd($sortieForm->isSubmitted());
//        dd($sortieForm->isValid());

if ($sortieForm->isSubmitted()){
dd($sortieForm->get('plus')->getData());

}
//            dd($sortieForm->get('plus')->isSubmitted());
        if($sortieForm->getClickedButton() ){
            return $this->redirectToRoute('lieu_creer');
        }
//
//        if( $sortieForm->get('plus')->isSubmitted()){
//            return $this->redirectToRoute('lieu_creer');
//        }


        if ($sortieForm->isSubmitted() && $sortieForm->isValid()){

            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Sortie créée avec Succès');

            // A modifier et rediriger vers la visualisation de sortie détail
            return $this->redirectToRoute('sortie_liste_sorties');
        }

        return $this->render('sortie/creer-sortie.html.twig', [
            'sortieForm' => $sortieForm->createView(),
            'campus' => $sortie->getCampus()->getNom(),

        ]);

    }



    #[Route('/sorties/inscrire/{id}', name: 'sorties_inscrire')]
    public function inscrire ($id,
                              SortieRepository $sortieRepository,
                              EntityManagerInterface $entityManager
                              ): Response
        //Accéder à s'inscrire
    { $assosPartiSort = new AssosPartiSort();
        $sortie = $sortieRepository->find($id);
        $assosPartiSort->setSortie($sortie)
                        ->setParticipant($this->getUser());

        //Si la sortie Etat = ouverte et dateDuJour > dateLimiteInscription et nbInscrits < nbInscriptionsMax
        //Alors on peut ajouter le participant à la liste des inscrits
       /* if ($etatOuverte && $dateLimiteInscription < CURRENT_DATE() && nbInscrit < nbInscriptionsMax ) */
        //Vérifier si le participant existe déjà avec une requête
        //findOneBy (where)


        //j'ajoute l'instance à l'objet Sortie
        $sortie->addAssosPartiSort($assosPartiSort);
        $entityManager->persist($sortie);
        $entityManager->persist($assosPartiSort);
        $entityManager->flush();

        return $this->render('inscrire/inscrire-sorties.html.twig', [
            'sortie'=> $sortie,

        ]);
    }


    #[Route('/sorties/desister/{id}', name: 'consulter_desister')]
    public function desister ($id
                                ): Response

    {
        //On peut se désister si inscrit && dateDebut < dateDuJour
        //nombre de places libre +1

        return $this->render('sortie/liste-sorties.html.twig', [

        ]);
    }



}
