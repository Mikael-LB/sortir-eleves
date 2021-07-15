<?php

namespace App\Controller;

use App\BO\Annuler;
use App\Entity\AssosPartiSort;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\BO\Filtrer;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\AnnulerType;
use App\Form\FiltrerType;
use App\Form\SortieModifierType;
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
use Symfony\Component\Serializer\SerializerInterface;

class SortieController extends AbstractController
{
    #[Route('/sorties', name: 'sortie_liste_sorties')]
    public function listeSorties(Request $request,
                                 SortieRepository $sortieRepository,
                                 EtatRepository $etatRepository,
                                 ParticipantRepository $participantRepository): Response
    {
        $participant = $this->getUser();
        $filtrer = new Filtrer();
        //initial fill with all results
        //without queryBuilder
        //$sorties = $sortieRepository->findBy([], ["dateHeureDebut" => "ASC"]);
        //gain a few request with doctrine by using the method with queryBuilder
        //on sorties before now
        $sorties=$sortieRepository->findForFilterForm($filtrer->setDateHeureDebut(new \DateTime('now')), $participant);

        $filtrerForm = $this->createForm(FiltrerType::class, $filtrer);

        $filtrerForm->handleRequest($request);

        if ($filtrerForm->isSubmitted() && $filtrerForm->isValid()) {

            //$participant = $participantRepository->findOneByEmail([$this->getUser()->getUsername()]);
            //$filtrer = $filtrerForm->getData();
            $sorties = $sortieRepository->findForFilterForm($filtrer, $participant);
        }

        return $this->render('sortie/liste-sorties.html.twig', [
            'filtrerForm' => $filtrerForm->createView(),
            'sorties' => $sorties,
        ]);
    }

//Méthode permettant d'afficher les informations concernant la sortie
    #[Route('/sorties/consulter/{id}', name: 'sorties_consulter', requirements: ['id' => '\d+'])]
    public function consulter($id,
                              SortieRepository $sortieRepository): Response
    {
        //Afficher les détails concernant une sortie
        $sortie = $sortieRepository->find($id);

        //Si l'état de ma sortie est ouverte ou historisée on ne pas accéder aux détails concernant cette sortie
        if($sortie->getEtat()->getLibelle() == 'En Création'){
            $this->addFlash('error','Vous ne pouvez pas consultez les informations concernant cette sortie car sortie en création');
            return $this->redirectToRoute('sortie_liste_sorties');
        }else if( $sortie->getEtat()->getLibelle() == 'Historisée'){
            $this->addFlash('error','Vous ne pouvez pas consultez les informations concernant cette sortie car sortie historisé');
            return  $this->redirectToRoute('sortie_liste_sorties');
        }

        //Retourne vers la vue des détails de la sortie si la sortie n'est ni en création, ni historisée
        return $this->render('consulter/consulter-sorties.html.twig', [
            'sortie' => $sortie,
        ]);
    }

    #[Route('/sorties/creer', name: 'sortie_creer')]
    public function creerSortie(SortieRepository $sortieRepository, EtatRepository $etatRepository, LieuRepository $lieuRepository, VilleRepository $villeRepository, EntityManagerInterface $entityManager, SerializerInterface $serializer, Request $request): Response
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


        $sortieForm = $this->createForm(SortieType::class, $sortie);

        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {

        $dataLieu = $sortieForm->get('Lieu')->getData();
        $sortie->setLieu($dataLieu);
        /**
         * @var $dataLieu Lieu
         */
        $dataLieu->addSorty($sortie);
        $entityManager->persist($dataLieu);
        $dataVille = $sortieForm->get('Ville')->getData();

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

    //Méthode permettant à l'utilisateur de s'inscrire
    #[Route('/sorties/inscrire/{id}', name: 'sorties_inscrire')]
    public function inscrire($id,
                             SortieRepository $sortieRepository,
                             EntityManagerInterface $entityManager,
                             AssosPartiSortRepository $assosPartiSortRepository): Response
    {

        //Je recherche une sortie(une instance potentiellement déjà existante) par sa clé primaire (id)
        $sortie = $sortieRepository->find($id);
        //Je crée la variable inscription dans laquelle je stocke les infos id de la sortie et id du participant
        $inscription = $assosPartiSortRepository->findOneBy(['sortie' => $sortie,
            'participant' => $this->getUser()]);
        //Je vérifie si dans ma base de données il existe déjà un inscrit à une sortie donnée c-à-d si la combinaison sortie et participant existe déjà
        if ($inscription) {
            $this->addFlash('error', 'Vous êtes déjà inscrit à cette sortie');
         return $this->redirectToRoute('sortie_liste_sorties');

        } else {
            //Si la sortie Etat = ouverte et dateDuJour > dateLimiteInscription et nbInscrits < nbInscriptionsMax
            if ($sortie->getEtat()->getLibelle() == 'Ouverte'
                && $sortie->getDateLimiteInscription() > new \DateTime()
                && $sortie->getNbInscriptionsMax() > count($sortie->getAssosPartiSort()))
            {
                //Je crée une nouvelle instance d'AssosPartiSort
                $assosPartiSort = new AssosPartiSort();
                //Je récupère les informations des attributs sortie et participant de assosPartiSort
                $assosPartiSort->setSortie($sortie)
                    ->setParticipant($this->getUser());
                //j'ajoute l'instance à l'objet Sortie
                $sortie->addAssosPartiSort($assosPartiSort);
                //Je valide l'enregistrement des instances assortPartiSort et de la sortie
                $entityManager->persist($sortie);
                $entityManager->persist($assosPartiSort);
                $entityManager->flush();
                   $this->addFlash('success','Vous êtes inscrit');
                return $this->redirectToRoute('sortie_liste_sorties');
            }else{
                $this->addFlash('error','plus de places ou datelimite dépassée ou sortie fermée');
              return $this->redirectToRoute('sortie_liste_sorties');
            }
        }
    }




    //Méthode permettant de désister à une sortie
    #[Route('/sorties/desister/{id}', name: 'sorties_desister')]
    public function desister($id, SortieRepository $sortieRepository,
                            EntityManagerInterface $entityManager,
    AssosPartiSortRepository $assosPartiSortRepository
    ): Response

    {
        //Je recherche une sortie(une instance potentiellement déjà existante) par sa clé primaire (id)
        $sortie = $sortieRepository->find($id);
        //Je récupère les infos dans la table d'association
        $inscription = $assosPartiSortRepository->findOneBy(['sortie' => $sortie,
            'participant' => $this->getUser()]);

        //Je vérifie si dans ma base de données il existe déjà cette inscription et si la date de début de sortie est une date future
        if ($inscription  && ($sortie->getDateHeureDebut() > new \DateTime())) {
            //Je valide la suppression de cette inscription
            $entityManager->remove($inscription);
            $entityManager->flush();
            $this->addFlash('success', 'Vous êtes désinscrit de cette sortie');
            return $this->redirectToRoute('sortie_liste_sorties');
        }else{
            $this->addFlash('error','Vous ne pouvez pas vous désinscrire car la date de la sortie est dépassée');
            return $this->redirectToRoute('sortie_liste_sorties');
        }

}



    #[Route('/sorties/annuler/{id}', name: 'sorties_annuler', requirements: ['id' => '\d+'])]
    public function annulerSortie(int $id,
                                  Request $request,
                                  SortieRepository $sortieRepository,
                                  EtatRepository $etatRepository,
                                  EntityManagerInterface $em): Response
    {
        $sortie = $sortieRepository->find($id);
        $organisateur = $this->getUser();

        //cancel a Sortie is allow only for the organisateur
        //the Sortie must not have started : the Etat must be Ouverte or Clôturée
        if ($sortie->getOrganisateur()->getUsername() == $organisateur->getUsername()
            && ($sortie->getEtat()->getLibelle() === 'Ouverte'
                || $sortie->getEtat()->getLibelle() === 'Clôturée')) {

            $annuler = new Annuler();
            $annulerForm = $this->createForm(AnnulerType::class, $annuler);
            $annulerForm->handleRequest($request);
            //form is submitted
            if ($annulerForm->isSubmitted()) {
                if ($annulerForm->isValid() && $annulerForm->get('enregistrer')->isClicked()) {
                    //try to protect against overflow
                    try {
                        //add motif at the beginning of infosSortie
                        $sortie->setInfosSortie(
                            "*** Annulée pour le motif : " . $annuler->getMotif() . " *** " . $sortie->getInfosSortie());
                        //etat
                        $etatFutur = $etatRepository->findOneByLibelle('Annulée');
                        $sortie->setEtat($etatFutur);

                        //persist
                        $em->persist($sortie);
                        $em->flush();
                        //flash message and return
                        $this->addFlash('success', 'Sortie annulée, son état est mis à : Annulée');
                    }catch (\Exception $e){
                        //flash message and return
                        $this->addFlash('error', 'Action non réalisable, le texte est trop long');
                        //perhaps some logs one day ?
                    }
                    //whatever the result is : redirect to list of Sortie
                    return $this->redirectToRoute('sortie_liste_sorties');
                }
                //if action cancel is cancelled
                if ($annulerForm->get('annuler')->isClicked()){
                    return $this->redirectToRoute('sortie_liste_sorties');
                }
            }
            //no submit, just show the twig page with empty form
            return $this->render('sortie/annuler-sortie.html.twig', [
                'annulerForm' => $annulerForm->createView(),
                'sortie' => $sortie,
            ]);
        }

        //if route is accessed by someone else than the organisateur, just redirect to list of Sortie
        return $this->redirectToRoute('sortie_liste_sorties');
    }

    #[Route('/sorties/modifier/{id}', name: 'sorties_modifier', requirements: ['id' => '\d+'])]
    public function modifierSortie(int $id, SortieRepository $sortieRepository,EtatRepository $etatRepository,EntityManagerInterface $entityManager, Request $request) :Response{

        $sortie = $sortieRepository->find($id);

        if ( ($sortie->getEtat()->getLibelle() != 'En Création') || ($sortie->getOrganisateur() != $this->getUser()) ){
            return $this->redirectToRoute('sortie_liste_sorties');
        }


        $sortieForm = $this->createForm(SortieModifierType::class, $sortie);
        $sortieForm->handleRequest($request);

        if($sortieForm->isSubmitted()){

        $dataLieu = $sortieForm->get('Lieu')->getData();
        if ($dataLieu !== null){
        $sortie->setLieu($dataLieu);
        /**
         * @var $dataLieu Lieu
         */
        $dataLieu->addSorty($sortie);
        $entityManager->persist($dataLieu);
        }
        $dataVille = $sortieForm->get('Ville')->getData();
        if ($sortieForm->isSubmitted() && 'supprimer' == ($sortieForm->getClickedButton()->getConfig()->getName())){
            $etatSortie =$sortie->getEtat();
            $etatSortie->removeSorty($sortie);
            $entityManager->persist($etatSortie);
            $entityManager->remove($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Sortie supprimée avec Succès');

            return $this->redirectToRoute('sortie_liste_sorties');
        }

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            if ('publier' == ($sortieForm->getClickedButton()->getConfig()->getName())) {
                $sortie->setEtat($etatRepository->findOneByLibelle(['Ouverte']));
            }
            ($sortie->getEtat())->addSorty($sortie);
            $entityManager->persist(($sortie->getEtat()));
            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Sortie modifiée avec Succès');

            // A modifier et rediriger vers la visualisation de sortie détail
            return $this->redirectToRoute('sortie_liste_sorties');
        }
        }

        return $this->render('sortie/modifier-sorties.html.twig',[
            'sortieForm' => $sortieForm->createView(),
            'campus' => $sortie->getCampus()->getNom(),
    ]);
    }

}
