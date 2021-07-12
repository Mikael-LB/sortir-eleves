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
use Symfony\Component\Serializer\SerializerInterface;

class SortieController extends AbstractController
{
    #[Route('/sorties', name: 'sortie_liste_sorties')]
    public function listeSorties(Request $request,
                                 SortieRepository $sortieRepository,
                                 EtatRepository $etatRepository,
                                 ParticipantRepository $participantRepository): Response
    {
        //initial fill with all results
        $sorties = $sortieRepository->findBy([],["dateHeureDebut"=>"ASC"]);

        $filtrer = new Filtrer();
        $filtrerForm = $this->createForm(FiltrerType::class,$filtrer);
        $filtrerForm->handleRequest($request);

        if($filtrerForm->isSubmitted() && $filtrerForm->isValid()){
            $participant = $this->getUser();
            //$participant = $participantRepository->findOneByEmail([$this->getUser()->getUsername()]);
            $filtrer = $filtrerForm->getData();
            $sorties = $sortieRepository->findForFilterForm($filtrer, $participant);
        }

        return $this->render('sortie/liste-sorties.html.twig', [
            'filtrerForm'=>$filtrerForm->createView(),
            'sorties' => $sorties,
        ]);
    }


    #[Route('/sorties/consulter/{id}', name: 'sorties_consulter', requirements:['id'=>'\d+'])]
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
    public function creerSortie(SortieRepository $sortieRepository, EtatRepository $etatRepository , LieuRepository $lieuRepository, VilleRepository $villeRepository, EntityManagerInterface $entityManager,SerializerInterface $serializer,Request $request): Response
    {
        $sortie = new Sortie();
        $organisateur = $this->getUser();
        /**
         * @var $organisateur Participant
         */
        $sortie->setOrganisateur($organisateur);
        $sortie->setCampus($organisateur->getCampus());
        $sortie->setEtat($etatRepository->findOneByLibelle(['En Création']));
//        $villes = $villeRepository->findAll();


        $sortieForm = $this->createForm(SortieType::class,$sortie);

        $sortieForm->handleRequest($request);
            $dataLieu = $sortieForm->get('Lieu')->getData();

            $sortie->setLieu($dataLieu);
            $dataVille = $sortieForm->get('Ville')->getData();


//        dd($sortie);
//        dd($sortieForm->isSubmitted());
//        dd($sortieForm->isValid());


        if ($sortieForm->isSubmitted() && $sortieForm->isValid()){

//            dd('publier' == ($sortieForm->getClickedButton()->getConfig()->getName()));

            if ('publier' == ($sortieForm->getClickedButton()->getConfig()->getName())){
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
    //TODO mettre en place le javascript pour modifier les rue code postal et autres

//        $villesJSON = $this->json($villeRepository->findAll(), 200, [], ['groups' =>'group_ville' ]);
//
//        $villesSerialize = $serializer->serialize($villes, 'json', ['groups' =>'group_ville']);
//        dd($villes, $villesJSON,$villesSerialize);

        return $this->render('sortie/creer-sortie.html.twig', [
            'sortieForm' => $sortieForm->createView(),
            'campus' => $sortie->getCampus()->getNom(),
//            'villes' => $villes,

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


    #[Route('/sorties/{id}', name: 'consulter_desister')]
    public function desister ($id
                                ): Response

    {
        //On peut se désister si inscrit && dateDebut < dateDuJour
        //nombre de places libre +1

        return $this->render('sortie/liste-sorties.html.twig', [

        ]);
    }



}
