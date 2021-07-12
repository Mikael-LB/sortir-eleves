<?php

namespace App\Controller;

use App\Entity\AssosPartiSort;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\BO\Filtrer;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\FiltrerType;
use App\Form\SortieType;
use App\Repository\AssosPartiSortRepository;
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
        //initial fill with all results
        $sorties = $sortieRepository->findBy([], ["dateHeureDebut" => "ASC"]);

        $filtrer = new Filtrer();
        $filtrerForm = $this->createForm(FiltrerType::class, $filtrer);
        $filtrerForm->handleRequest($request);

        if ($filtrerForm->isSubmitted() && $filtrerForm->isValid()) {
            $participant = $this->getUser();
            //$participant = $participantRepository->findOneByEmail([$this->getUser()->getUsername()]);
            $filtrer = $filtrerForm->getData();
            $sorties = $sortieRepository->findForFilterForm($filtrer, $participant);
        }

        return $this->render('sortie/liste-sorties.html.twig', [
            'filtrerForm' => $filtrerForm->createView(),
            'sorties' => $sorties,
        ]);
    }


    #[Route('/sorties/consulter/{id}', name: 'sorties_consulter', requirements: ['id' => '\d+'])]
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
            'ville' => $ville,
            'participants' => $participants
        ]);
    }

    #[Route('/sorties/creer', name: 'sortie_creer')]
    public function creerSortie(SortieRepository $sortieRepository, EtatRepository $etatRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $sortie = new Sortie();
        $organisateur = $this->getUser();
        /**
         * @var $organisateur Participant
         */
        $sortie->setOrganisateur($organisateur);
        $sortie->setCampus($organisateur->getCampus());
        $sortie->setEtat($etatRepository->findOneByLibelle(['En Création']));


        $sortieForm = $this->createForm(SortieType::class, $sortie);

        $sortieForm->handleRequest($request);
        $dataLieu = $sortieForm->get('Lieu')->getData();

        $sortie->setLieu($dataLieu);
        $dataVille = $sortieForm->get('Ville')->getData();


//        dd($sortie);
//        dd($sortieForm->isSubmitted());
//        dd($sortieForm->isValid());


        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {

//            dd('publier' == ($sortieForm->getClickedButton()->getConfig()->getName()));

            if ('publier' == ($sortieForm->getClickedButton()->getConfig()->getName())) {
                $sortie->setEtat($etatRepository->findOneByLibelle(['Ouverte']));

            }

            ($sortie->getEtat())->addSorty($sortie);
            $entityManager->persist(($sortie->getEtat()));
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
    public function inscrire($id,
                             SortieRepository $sortieRepository,
                             EntityManagerInterface $entityManager,
                             AssosPartiSortRepository $assosPartiSortRepository): Response
    {

        //Je recherche une sortie(une instance potentiellement déjà existante) par sa clé primaire (id)
        $sortie = $sortieRepository->find($id);
        $inscription = $assosPartiSortRepository->findOneBy(['sortie' => $sortie,
            'participant' => $this->getUser()]);
        //Je vérifie si dans ma base de données il existe déjà un inscrit à une sortie donnée c-à-d si la combinaison sortie et participant existe déjà
        if ($inscription) {
            $this->addFlash('pas possible', 'Vous êtes déjà inscrit à cette sortie');
         return $this->redirectToRoute('sortie_liste_sorties');

        } else {
            //Si la sortie Etat = ouverte et dateDuJour > dateLimiteInscription et nbInscrits < nbInscriptionsMax
            if ($sortie->getEtat()->getLibelle() == 'Ouverte'
                && $sortie->getDateLimiteInscription() > new \DateTime()
                && $sortie->getNbInscriptionsMax() > count($sortie->getAssosPartiSort()))
            {
                //Je crée une nouvelle instance d'AssosPartiSort
                $assosPartiSort = new AssosPartiSort();
                //Je récupère les informations des attributs sorttie et participant de assosPartiSort
                $assosPartiSort->setSortie($sortie)
                    ->setParticipant($this->getUser());
                //j'ajoute l'instance à l'objet Sortie
                $sortie->addAssosPartiSort($assosPartiSort);
                $entityManager->persist($sortie);
                $entityManager->persist($assosPartiSort);
                $entityManager->flush();
                return $this->render('inscrire/inscrire-sorties.html.twig', [
                    'sortie' => $sortie,
                ]);
            }else{
                $this->addFlash('impossible','plus de places ou datelimite dépassée ou sortie fermée');
              return $this->redirectToRoute('sortie_liste_sorties');
            }
        }
    }


    /*$sortie->getEtat()->$etatRepository->findOneByLib('ouverte');
           //findBy([libelle =>'ouverte'])
           //OU $var = $sortie->getEtat();
           $var2 = $var->getLibelle();
           $var2 == 'Ouverte'*/


    #[Route('/sorties/desister/{id}', name: 'sorties_desister')]
    public function desister($id, SortieRepository $sortieRepository,
                            EntityManagerInterface $entityManager,
    AssosPartiSortRepository $assosPartiSortRepository
    ): Response

    {
        //Je recherche une sortie(une instance potentiellement déjà existante) par sa clé primaire (id)
        $sortie = $sortieRepository->find($id);
        $inscription = $assosPartiSortRepository->findOneBy(['sortie' => $sortie,
            'participant' => $this->getUser()]);
        //Je vérifie si dans ma base de données il existe déjà un inscrit à une sortie donnée c-à-d si la combinaison sortie et participant existe déjà
        if ($inscription && $sortie->getDateHeureDebut() > new \DateTime()) {

            //Je crée une nouvelle instance d'AssosPartiSort
            $assosPartiSort = new AssosPartiSort();
            //Je récupère les informations des attributs sortie et participant de assosPartiSort
            $assosPartiSort->setSortie($sortie)
                ->setParticipant($this->getUser());
            $entityManager->remove($assosPartiSortRepository);
            $this->addFlash('OK','Vous êtes désinscrit à cette sortie');

            return $this->render('inscrire/inscrire-sorties.html.twig', [
                'sortie' => $sortie,
            ]);
        }

    }


}
