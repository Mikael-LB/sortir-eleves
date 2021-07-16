<?php

namespace App\DataFixtures;

use App\DataFixtures\Utils\FixturesService;
use App\Entity\AssosPartiSort;
use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Repository\AssosPartiSortRepository;
use App\Repository\CampusRepository;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    protected $NOM_CAMPUS = ['Rennes','Quimper','Niort','Nantes'];
    protected $NOM_ETAT = ['En Création','Ouverte','Clôturée','En Cours', 'Terminée', 'Annulée','Historisée'];
    protected $campusRepository;
    protected $villeRepository;
    protected $lieuRepository;
    protected $etatRepository;
    protected $participantRepository;
    protected $sortieRepository;
    protected $assosPartiSortRepository;
    protected $passwordEncoder;
    protected $fixturesService;


    public function __construct(CampusRepository $campusRepository,
                                VilleRepository $villeRepository,
                                LieuRepository $lieuRepository,
                                EtatRepository $etatRepository,
                                ParticipantRepository $participantRepository,
                                SortieRepository $sortieRepository,
                                AssosPartiSortRepository $assosPartiSortRepository,
                                UserPasswordEncoderInterface $passwordEncoder,
                                FixturesService $fixturesService,
    ){
    $this->campusRepository = $campusRepository;
    $this->villeRepository = $villeRepository;
    $this->lieuRepository = $lieuRepository;
    $this->etatRepository = $etatRepository;
    $this->participantRepository = $participantRepository;
    $this->sortieRepository= $sortieRepository;
    $this->assosPartiSortRepository =$assosPartiSortRepository;
    $this->passwordEncoder = $passwordEncoder;
    $this->fixturesService = $fixturesService;
    }

    public function load(ObjectManager $manager)
    {
        $generator = Factory::create('fr_FR');


        //Création des campus
//        $NOM_CAMPUS = ["Rennes", "Quimper", "Niort", "Nantes"];

        foreach ($this->NOM_CAMPUS as $nomCampus) {
            $campus = new Campus();
            $campus->setNom(strval($nomCampus));

            $manager->persist($campus);
        }
        $manager->flush();

        //On récupère les campus
        $campusList = $this->campusRepository->findAll();
        $nbCampus = count($campusList);


        //Création des participants
        $participant= new Participant();
        $participant->setNom('Terrieur')
            ->setEmail('a@mail.com')
            ->setPrenom('Alain')
            ->setPassword($this->passwordEncoder->encodePassword($participant, 'password'))
            ->setTelephone('02 22 33 44 55 66')
            ->setPseudo('JeDébug')
            ->setEstAdministrateur(true)
            ->setCompteActif(true)
            ->setRoles(["ROLE_USER"])
            ->setCampus($campusList[1])
        ;
        $manager->persist($participant);
        $campus->addParticipant($participant);
        $manager->persist($campus);

        //Add a Participant with role Admin
        $adminParticipant= new Participant();
        $adminParticipant->setNom('Istrator')
            ->setEmail('admin@mail.com')
            ->setPrenom('Admin')
            ->setPassword($this->passwordEncoder->encodePassword($adminParticipant, 'password'))
            ->setTelephone('02 22 33 44 55 66')
            ->setPseudo('Root')
            ->setEstAdministrateur(true)
            ->setCompteActif(true)
            ->setRoles(["ROLE_ADMIN"])
            ->setCampus($campusList[1])
        ;
        $manager->persist($adminParticipant);
        $campus->addParticipant($adminParticipant);
        $manager->persist($campus);

        //Création des participants
        for ($i = 0; $i < $nbCampus; $i++) {
            $bool = true;
            for ($j = 0; $j< 5; $j++){
                $participant= new Participant();
                $participant->setNom($generator->lastName)
                    ->setEmail($generator->email)
                    ->setPrenom($generator->firstName)
                    ->setPassword($this->passwordEncoder->encodePassword($participant, 'password'))
                    ->setTelephone($generator->phoneNumber)
                    ->setPseudo($generator->userName)
                    ->setEstAdministrateur(false)
                    ->setCompteActif(true)
                    ->setRoles(["ROLE_USER"])
                    ->setCampus($campusList[$i])
                ;
                if($bool){
                    $participant->setUrlImage('generic-man-heroe.jpeg');
                    $bool = false;
                }else{
                    $participant->setUrlImage('generic-woman-heroe.jpeg');
                    $bool = true;
                }
                $campusList[$i]->addParticipant($participant);

                $manager->persist($participant);
                $manager->persist($campusList[$i]);
            }
        }
        $manager->flush();

        // On récupère tout nos participants
        $participantList = $this->participantRepository->findAll();
        $nbParticipantTotal = count($participantList);

        // Création des villes

        for ($i=0; $i<15 ; $i++){
            $ville = new Ville();
            $ville->setNom($generator->city)
                ->setCodePostal($generator->postcode)
            ;
            $manager->persist($ville);
        }
        $manager->flush();
        // On récupère les villes
        $villeList = $this->villeRepository->findAll();
        $nbVilles = count($villeList);


        // Création des états


        foreach ($this->NOM_ETAT as $etatNom) {
            $etat = new Etat();
            $etat->setLibelle(strval($etatNom));

            $manager->persist($etat);
        }
        $manager->flush();

        //On récupère les états
        $etatList = $this->etatRepository->findAll();
        $nbEtat =count($etatList);

        //Création des lieux
        for ($i=0; $i<$nbVilles; $i++)
        {

                $nomVille = $villeList[$i]->getNom();
            for ($j=0; $j<3; $j++)
            {
                $lieu = new Lieu();
                $lieu->setNom($generator->company . ' près de ' . $nomVille)
                    ->setRue($generator->streetName)
                    ->setLatitude($generator->latitude)
                    ->setLongitude($generator->longitude)
                    ->setVille($villeList[$i]);
                $villeList[$i]->addLieux($lieu);

                $manager->persist($lieu);
                $manager->persist($villeList[$i]);
            }
        }
            $manager->flush();
        //On récupère les lieux
        $lieuList = $this->lieuRepository->findAll();
        $nbLieu = count($lieuList);

        $nowDate = new \DateTime('now');
        // On récupère tout les états qui vont nous etre utiles
        // à differents moments de la création de sorties
        $etatAnnulee = $this->etatRepository->findOneByLibelle(['Annulée']);
        $etatHistorisee = $this->etatRepository->findOneByLibelle(['Historisée']);
        $etatCloturee = $this->etatRepository->findOneByLibelle(['Clôturée']);
        $etatTerminee = $this->etatRepository->findOneByLibelle(['Terminée']);
        $etatOuverte = $this->etatRepository->findOneByLibelle(['Ouverte']);
        $etatEnCours = $this->etatRepository->findOneByLibelle(['En Cours']);
        $etatEnCreation = $this->etatRepository->findOneByLibelle(['En Création']);


        // Création des Sortie avec AssosPartiSort
        // et le référencement des lieux et états
        for ($i=0; $i< 100; $i++){

            $sortie = new Sortie();
            $nbMaxParticipant = $generator->numberBetween(2, 20);
            $dateHeureDebut = $generator->dateTimeBetween('-2 months','+2 months');
//            $dateHeureDebut = $generator->dateTimeBetween('-2 months','+2 months');
            $dateLimiteInscription = new \DateTime($dateHeureDebut->format('Y-m-d H:i:s'));
            date_sub($dateLimiteInscription, date_interval_create_from_date_string('2 days'));
//            $duree = $generator->numberBetween(30, 10080);
            // 30 min | 10080 -> 7 jour | 43200 -> 1 mois
            $duree = $generator->numberBetween(43200, 43210);
            $dateHeureFin = new \DateTime($dateHeureDebut->format('Y-m-d H:i:s'));
            date_modify($dateHeureFin, '+'.$duree.' minutes');
            $dateHistorisation = new \DateTime($dateHeureFin->format('Y-m-d H:i:s'));
            date_modify($dateHistorisation, '+1 months');

            $organisateur = $participantList[$generator->numberBetween(0,$nbParticipantTotal-1)];
            $lieuSelectionnee = $lieuList[$generator->numberBetween(0, $nbLieu-1)];
            $sortie->setNbInscriptionsMax($nbMaxParticipant)
                ->setNom($generator->realText(155, 2))
                ->setDuree($duree)
                ->setInfosSortie($generator->realText(300,2))
                ->setDateHeureDebut($dateHeureDebut)
                ->setDateLimiteInscription($dateLimiteInscription)
                ->setNbInscriptionsMax($nbMaxParticipant)
                ->setOrganisateur($organisateur)
                ->setCampus($organisateur->getCampus())
                ->setLieu($lieuSelectionnee)
            ;
            // On ajoute sa sortie à l'organisateur ET le lieu
            $organisateur->addSorty($sortie);
            $lieuSelectionnee->addSorty($sortie);

            $manager->persist($organisateur);
            $manager->persist($lieuSelectionnee);





            // on remplie une sortie avec le nombre max de participant
            if ($generator->boolean(60)){
                for ($j=0; $j<$nbMaxParticipant; $j++){
                    $participant = $participantList[$generator->numberBetween(0,$nbParticipantTotal-1)];
                    // Si le participant choisi aléatoirement est l'organisateur on ne l'ajoute PAS
                    // aux participants de la sortie
                    if($participant === $organisateur){
                        $j--;
                    // Sinon on peut l'ajouter à la sortie
                    }else{
                        $ok = $this->fixturesService->addParticipantIfNotAlreadyInscrit($participant, $sortie, $manager);
                        if(!$ok){
                            $j--;
                        }
                    }
                }
            }
            // on ne remplie PAS totalement la sortie de participants
            elseif ($generator->boolean(50)){
                for ($j=0; $j<($nbMaxParticipant-1); $j++){
                    $participant = $participantList[$generator->numberBetween(0,$nbParticipantTotal-1)];
                    // Si le participant choisi aléatoirement est l'organisateur on ne l'ajoute PAS
                    // aux participants de la sortie
                    if($participant === $organisateur){
                        $j--;
                        // Sinon on peut l'ajouter à la sortie
                    }else{
                        $ok = $this->fixturesService->addParticipantIfNotAlreadyInscrit($participant, $sortie, $manager);
                        if(!$ok){
                            $j--;
                        }
                    }
                }
            }

            /* ANCIENNE ATTRIBUTION DES ETATS

            // On annule la sortie
            else{
                for ($j=0; $j<($nbMaxParticipant-1); $j++){
                    $participant = $participantList[$generator->numberBetween(0,$nbParticipantTotal-1)];
                    // Si le participant choisi aléatoirement est l'organisateur on ne l'ajoute PAS
                    // aux participants de la sortie
                    if($participant === $organisateur){
                        $j--;
                        // Sinon on peut l'ajouter à la sortie
                    }else{
                        $assosPartiSort = new AssosPartiSort();
                        $assosPartiSort->setSortie($sortie)
                            ->setParticipant($participant)
                        ;
                        $sortie->addAssosPartiSort($assosPartiSort);
                        $participant->addAssosPartiSort($assosPartiSort);
                        $manager->persist($participant);
                        $manager->persist($assosPartiSort);
                        $sortie->setEtat($etatAnnulee);

                        $etatAnnulee->addSorty($sortie);
                    }
                }
            }
            // On historise toutes les sorties annulée ou finies depuis plus d'un mois
            if ($dateHistorisation < $nowDate){
                $sortie->setEtat($etatHistorisee);
                $etatHistorisee->addSorty($sortie);
            }
            // selection des Etats correspondant à la sortie (sauf annulée)
            elseif ($sortie->getEtat() !== $etatAnnulee){

                // selection Ouverte || Clôturée
                if($dateLimiteInscription < $nowDate){
                    $nbParticipantInscris = count($sortie->getAssosPartiSort());

                    if ($nbParticipantInscris == $nbMaxParticipant){
                        $sortie->setEtat($etatCloturee);
                        $etatCloturee->addSorty($sortie);

                    }else{
                        $sortie->setEtat($etatOuverte);
                        $etatOuverte->addSorty($sortie);

                    }
                }
                // Selection Clôturée
                elseif( ($dateLimiteInscription > $nowDate) && ($dateHeureDebut > $nowDate) ) {
                    $sortie->setEtat($etatCloturee);
                    $etatCloturee->addSorty($sortie);

                }
                // selection En cours
                elseif( ($dateHeureDebut > $nowDate) && ( $dateHeureFin < $nowDate ) ){
                    $sortie->setEtat($etatEnCours);
                    $etatEnCours->addSorty($sortie);

                }
                elseif ( $dateHeureFin > $nowDate ){
                    $sortie->setEtat($etatTerminee);
                    $etatTerminee->addSorty($sortie);
                }
                else {
                    $sortie->setEtat( $etatEnCreation);
                    $etatEnCreation->addSorty($sortie);
                }
            }

Fin ancienne version  */

//            $etatAnnulee = $this->etatRepository->findOneByLibelle(['Annulée']);
//            $etatHistorisee = $this->etatRepository->findOneByLibelle(['Historisée']);
//            $etatCloturee = $this->etatRepository->findOneByLibelle(['Clôturée']);
//            $etatTerminee = $this->etatRepository->findOneByLibelle(['Terminée']);
//            $etatOuverte = $this->etatRepository->findOneByLibelle(['Ouverte']);
//            $etatEnCours = $this->etatRepository->findOneByLibelle(['En Cours']);
//            $etatEnCreation = $this->etatRepository->findOneByLibelle(['En Création']);



            $selectionEtat = $generator->numberBetween(1,7);
            switch ($selectionEtat){
                case 1 :
                    $sortie->setEtat($etatEnCreation);
                    $etatEnCreation->addSorty($sortie);
                    break;
                case 2 :

                    $sortie->setEtat($etatOuverte);
                    $etatOuverte->addSorty($sortie);
                    break;

                case 3 :

                    $sortie->setEtat($etatCloturee);
                    $etatCloturee->addSorty($sortie);
                    break;
                case 4 :

                    $sortie->setEtat($etatEnCours);
                    $etatEnCours->addSorty($sortie);
                    break;

                case 5 :

                    $sortie->setEtat($etatAnnulee);
                    $etatAnnulee->addSorty($sortie);
                    break;
                case 6 :

                    $sortie->setEtat($etatTerminee);
                    $etatTerminee->addSorty($sortie);
                    break;

                case 7 :

                    $sortie->setEtat($etatHistorisee);
                    $etatHistorisee->addSorty($sortie);
                    break;

                default :
                    printf(ERROR, 'BUG DANS LE SWITCH');
                    break;


            }



            // On fait persister notre sortie
            $manager->persist($sortie);
        }
            // On oublie pas de faire persister les Etats avec les ajout de sorties
        $manager->persist($etatAnnulee);
        $manager->persist($etatHistorisee);
        $manager->persist($etatCloturee);
        $manager->persist($etatTerminee);
        $manager->persist($etatOuverte);
        $manager->persist($etatEnCours);
        $manager->persist($etatEnCreation);

        $manager->flush();
    }


}
