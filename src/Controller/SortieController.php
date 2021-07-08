<?php

namespace App\Controller;

use App\BO\Filtrer;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\FiltrerType;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Util\Type;
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
            //TODO
        }

        return $this->render('sortie/liste-sorties.html.twig', [
            'filtrerForm'=>$filtrerForm->createView(),
            'sorties' => $sorties,
        ]);
    }

    #[Route('/sorties/creer', name: 'sortie_creer')]
    public function creerSortie(SortieRepository $sortieRepository, EntityManagerInterface $entityManager,Request $request): Response{
        $sortie = new Sortie();
        /**
         * @var (type= Participant)
         */
        $organisateur = $this->getUser();

        $sortie->setParticipant($organisateur);
        $sortie->setCampus($organisateur->getCampus());

        $sortieForm = $this->createForm(SortieType::class,$sortie);

        $sortieForm->handleRequest($request);

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

}
