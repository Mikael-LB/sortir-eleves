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

        $filtrerForm = $this->createForm(FiltrerType::class, $filtrer);

        $filtrerForm->handleRequest($request);

        if ($filtrerForm->isSubmitted() && $filtrerForm->isValid()) {

            //$participant = $participantRepository->findOneByEmail([$this->getUser()->getUsername()]);
            //$filtrer = $filtrerForm->getData();
            $sorties = $sortieRepository->findForFilterForm($filtrer, $participant);
        }else {
            //initial fill with all results
            //without queryBuilder
            //$sorties = $sortieRepository->findBy([], ["dateHeureDebut" => "ASC"]);
            //gain a few request with doctrine by using the method with queryBuilder
            //on sorties before now
            $now = new \DateTime('now');
            $nowPlus1Month = (new \DateTime('now'))->modify('+1 month');
            $sorties=$sortieRepository->findForFilterForm($filtrer->setDateHeureDebut($now)->setDateHeureFin($nowPlus1Month), $participant);
        }

        return $this->render('sortie/liste-sorties.html.twig', [
            'filtrerForm' => $filtrerForm->createView(),
            'sorties' => $sorties,
        ]);
    }

//M??thode permettant d'afficher les informations concernant la sortie
    #[Route('/sorties/consulter/{id}', name: 'sorties_consulter', requirements: ['id' => '\d+'])]
    public function consulter($id,
                              SortieRepository $sortieRepository): Response
    {
        //Afficher les d??tails concernant une sortie
        $sortie = $sortieRepository->find($id);

        //Si l'??tat de ma sortie est ouverte ou historis??e on ne pas acc??der aux d??tails concernant cette sortie
        if($sortie->getEtat()->getLibelle() == 'En Cr??ation'){
            $this->addFlash('error','Vous ne pouvez pas consultez les informations concernant cette sortie car sortie en cr??ation');
            return $this->redirectToRoute('sortie_liste_sorties');
        }else if( $sortie->getEtat()->getLibelle() == 'Historis??e'){
            $this->addFlash('error','Vous ne pouvez pas consultez les informations concernant cette sortie car sortie historis??');
            return  $this->redirectToRoute('sortie_liste_sorties');
        }

        //Retourne vers la vue des d??tails de la sortie si la sortie n'est ni en cr??ation, ni historis??e
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
        $sortie->setEtat($etatRepository->findOneByLibelle(['En Cr??ation']));
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

            $this->addFlash('success', 'Sortie cr????e avec Succ??s');

            // A modifier et rediriger vers la visualisation de sortie d??tail
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

    //M??thode permettant ?? l'utilisateur de s'inscrire
    #[Route('/sorties/inscrire/{id}', name: 'sorties_inscrire')]
    public function inscrire($id,
                             SortieRepository $sortieRepository,
                             EntityManagerInterface $entityManager,
                             AssosPartiSortRepository $assosPartiSortRepository): Response
    {

        //Je recherche une sortie(une instance potentiellement d??j?? existante) par sa cl?? primaire (id)
        $sortie = $sortieRepository->find($id);
        //Je cr??e la variable inscription dans laquelle je stocke les infos id de la sortie et id du participant
        $inscription = $assosPartiSortRepository->findOneBy(['sortie' => $sortie,
            'participant' => $this->getUser()]);
        //Je v??rifie si dans ma base de donn??es il existe d??j?? un inscrit ?? une sortie donn??e c-??-d si la combinaison sortie et participant existe d??j??
        if ($inscription) {
            $this->addFlash('error', 'Vous ??tes d??j?? inscrit ?? cette sortie');
         return $this->redirectToRoute('sortie_liste_sorties');

        } else {
            //Si la sortie Etat = ouverte et dateDuJour > dateLimiteInscription et nbInscrits < nbInscriptionsMax
            if ($sortie->getEtat()->getLibelle() == 'Ouverte'
                && $sortie->getDateLimiteInscription() > new \DateTime()
                && $sortie->getNbInscriptionsMax() > count($sortie->getAssosPartiSort()))
            {
                //Je cr??e une nouvelle instance d'AssosPartiSort
                $assosPartiSort = new AssosPartiSort();
                //Je r??cup??re les informations des attributs sortie et participant de assosPartiSort
                $assosPartiSort->setSortie($sortie)
                    ->setParticipant($this->getUser());
                //j'ajoute l'instance ?? l'objet Sortie
                $sortie->addAssosPartiSort($assosPartiSort);
                //Je valide l'enregistrement des instances assortPartiSort et de la sortie
                $entityManager->persist($sortie);
                $entityManager->persist($assosPartiSort);
                $entityManager->flush();
                   $this->addFlash('success','Vous ??tes inscrit');
                return $this->redirectToRoute('sortie_liste_sorties');
            }else{
                $this->addFlash('error','plus de places ou datelimite d??pass??e ou sortie ferm??e');
              return $this->redirectToRoute('sortie_liste_sorties');
            }
        }
    }




    //M??thode permettant de d??sister ?? une sortie
    #[Route('/sorties/desister/{id}', name: 'sorties_desister')]
    public function desister($id, SortieRepository $sortieRepository,
                            EntityManagerInterface $entityManager,
    AssosPartiSortRepository $assosPartiSortRepository
    ): Response

    {
        //Je recherche une sortie(une instance potentiellement d??j?? existante) par sa cl?? primaire (id)
        $sortie = $sortieRepository->find($id);
        //Je r??cup??re les infos dans la table d'association
        $inscription = $assosPartiSortRepository->findOneBy(['sortie' => $sortie,
            'participant' => $this->getUser()]);

        //Je v??rifie si dans ma base de donn??es il existe d??j?? cette inscription et si la date de d??but de sortie est une date future
        if ($inscription  && ($sortie->getDateHeureDebut() > new \DateTime())) {
            //Je valide la suppression de cette inscription
            $entityManager->remove($inscription);
            $entityManager->flush();
            $this->addFlash('success', 'Vous ??tes d??sinscrit de cette sortie');
            return $this->redirectToRoute('sortie_liste_sorties');
        }else{
            $this->addFlash('error','Vous ne pouvez pas vous d??sinscrire car la date de la sortie est d??pass??e');
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
        //the Sortie must not have started : the Etat must be Ouverte or Cl??tur??e
        if ($sortie->getOrganisateur()->getUsername() == $organisateur->getUsername()
            && ($sortie->getEtat()->getLibelle() === 'Ouverte'
                || $sortie->getEtat()->getLibelle() === 'Cl??tur??e')) {

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
                            "*** Annul??e pour le motif : " . $annuler->getMotif() . " *** " . $sortie->getInfosSortie());
                        //etat
                        $etatFutur = $etatRepository->findOneByLibelle('Annul??e');
                        $sortie->setEtat($etatFutur);

                        //persist
                        $em->persist($sortie);
                        $em->flush();
                        //flash message and return
                        $this->addFlash('success', 'Sortie annul??e, son ??tat est mis ?? : Annul??e');
                    }catch (\Exception $e){
                        //flash message and return
                        $this->addFlash('error', 'Action non r??alisable, le texte est trop long');
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

        if ( ($sortie->getEtat()->getLibelle() != 'En Cr??ation') || ($sortie->getOrganisateur() != $this->getUser()) ){
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

            $this->addFlash('success', 'Sortie supprim??e avec Succ??s');

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

            $this->addFlash('success', 'Sortie modifi??e avec Succ??s');

            // A modifier et rediriger vers la visualisation de sortie d??tail
            return $this->redirectToRoute('sortie_liste_sorties');
        }
        }

        return $this->render('sortie/modifier-sorties.html.twig',[
            'sortieForm' => $sortieForm->createView(),
            'campus' => $sortie->getCampus()->getNom(),
    ]);
    }

}
