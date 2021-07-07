<?php

namespace App\DataFixtures;

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
    protected $NOM_ETAT = ['En Création','Ouverte','Fermée','En Cours', 'Terminée', 'Annulée','Historisée'];
    protected $campusRepository;
    protected $villeRepository;
    protected $lieuRepository;
    protected $etatRepository;
    protected $participantRepository;
    protected $sortieRepository;
    protected $assosPartiSortRepository;
    protected $passwordEncoder;


    public function __construct(CampusRepository $campusRepository,
                                VilleRepository $villeRepository,
                                LieuRepository $lieuRepository,
                                EtatRepository $etatRepository,
                                ParticipantRepository $participantRepository,
                                SortieRepository $sortieRepository,
                                AssosPartiSortRepository $assosPartiSortRepository,
                                UserPasswordEncoderInterface $passwordEncoder
    ){
    $this->campusRepository = $campusRepository;
    $this->villeRepository = $villeRepository;
    $this->lieuRepository = $lieuRepository;
    $this->etatRepository = $etatRepository;
    $this->participantRepository = $participantRepository;
    $this->sortieRepository= $sortieRepository;
    $this->assosPartiSortRepository =$assosPartiSortRepository;
    $this->passwordEncoder = $passwordEncoder;
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
        for ($i = 0; $i < $nbCampus; $i++) {
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
                    ->setCampus($campusList[$i])
                ;
                $manager->persist($participant);
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

                $manager->persist($lieu);
            }
        }
            $manager->flush();
        //On récupère les lieux
        $lieuList = $this->lieuRepository->findAll();
        $nbLieu = count($lieuList);

        // Créaiton des Sortie avec AssosPartiSort
//
//        for ($i=0; $i< 100; $i++){
//
//            $sortie = new Sortie();
//            $nbMaxParticipant = $generator->numberBetween(2, 20);
//            $dateHeureDebut = $generator->dateTimeBetween('-2 months','+2 months');
//            $dateLimiteInscription = new \DateTime(($dateHeureDebut->format('Y-m-d H:i:s')));
//            date_sub($dateLimiteInscription, date_interval_create_from_date_string('2 days'));
//            $duree = $generator->time('H:i:s');
//            $dateHeureFin = new \DateTime($dateHeureDebut);
//            date_add($dateHeureFin, 'minutes');
//
//
//
////            $dateLimiteInscriptionformatee = date_format($dateLimiteInscription, "d/m/Y");
//
//            $organisateur = $participantList[$generator->numberBetween(0,$nbParticipantTotal)];
//            $sortie->setNbInscriptionsMax($nbMaxParticipant)
//                ->setNom($generator->realText(155, 2))
//                ->setDuree($duree)
//                ->setInfosSortie($generator->realText(300,2))
//                ->setDateHeureDebut($dateHeureDebut)
//                ->setDateLimiteInscription($dateLimiteInscription)
//                ->setNbInscriptionsMax($nbMaxParticipant)
//                ->setParticipant($organisateur)
//                ->setCampus($organisateur->getCampus())
//                ->setLieu($lieuList[$generator->numberBetween(0, $nbLieu)])
//            ;
//            // On ajoute sa sotrie à l'organisateur
//            $organisateur->addSorty($sortie);
//            $manager->persist($organisateur);
//            // on remplie une sortie avec le nombre max de participant
//            if ($generator->boolean(60)){
//                for ($j=0; $j<$nbMaxParticipant; $j++){
//                    $participant = $participantList[$generator->numberBetween(0,$nbParticipantTotal)];
//                    // Si le participant choisi aléatoirement est l'organisateur on ne l'ajoute PAS
//                    // aux participants de la sortie
//                    if($participant == $organisateur){
//                        $j--;
//                    // Sinon on peut l'ajouter à la sortie
//                    }else{
//                        $assosPartiSort = new AssosPartiSort();
//                        $assosPartiSort->setSortie($sortie)
//                            ->setParticipant($participant)
//                            ;
//                        $sortie->addAssosPartiSort($assosPartiSort);
//                        $participant->addAssosPartiSort($assosPartiSort);
//                        $manager->persist($participant);
//                        $manager->persist($assosPartiSort);
//                    }
//                }
//            }
//            // on ne remplie PAS totalement la sortie de participants
//            elseif ($generator->boolean(50)){
//                for ($j=0; $j<($nbMaxParticipant-1); $j++){
//                    $participant = $participantList[$generator->numberBetween(0,$nbParticipantTotal)];
//                    // Si le participant choisi aléatoirement est l'organisateur on ne l'ajoute PAS
//                    // aux participants de la sortie
//                    if($participant == $organisateur){
//                        $j--;
//                        // Sinon on peut l'ajouter à la sortie
//                    }else{
//                        $assosPartiSort = new AssosPartiSort();
//                        $assosPartiSort->setSortie($sortie)
//                            ->setParticipant($participant)
//                        ;
//                        $sortie->addAssosPartiSort($assosPartiSort);
//                        $participant->addAssosPartiSort($assosPartiSort);
//                        $manager->persist($participant);
//                        $manager->persist($assosPartiSort);
//                    }
//                }
//            }
//            // On annule la sortie
//            else{
//                for ($j=0; $j<($nbMaxParticipant-1); $j++){
//                    $participant = $participantList[$generator->numberBetween(0,$nbParticipantTotal)];
//                    // Si le participant choisi aléatoirement est l'organisateur on ne l'ajoute PAS
//                    // aux participants de la sortie
//                    if($participant == $organisateur){
//                        $j--;
//                        // Sinon on peut l'ajouter à la sortie
//                    }else{
//                        $assosPartiSort = new AssosPartiSort();
//                        $assosPartiSort->setSortie($sortie)
//                            ->setParticipant($participant)
//                        ;
//                        $sortie->addAssosPartiSort($assosPartiSort);
//                        $participant->addAssosPartiSort($assosPartiSort);
//                        $manager->persist($participant);
//                        $manager->persist($assosPartiSort);
//                        $sortie->setEtat($this->etatRepository->findOneByLibelle(['Annulée']));
//                    }
//                }
//            }
//            // selection des Etats correspondant à la sortie (sauf annulée)
//
//            if ($sortie->getEtat() !== $this->etatRepository->findOneByLibelle(['Annulée'])){
//
//                // selection Ouverte || Fermée
//                if($dateLimiteInscription < new \DateTime('now')){
//                    $nbParticipantInscris = count($sortie->getAssosPartiSort());
//
//                    if ($nbParticipantInscris == $nbMaxParticipant){
//                        $sortie->setEtat($this->etatRepository->findOneByLibelle(['Fermée']));
//                    }else{
//                        $sortie->setEtat($this->etatRepository->findOneByLibelle(['Ouverte']));
//                    }
//                }
//
//                // selection En cours
//                if( ($dateHeureDebut < new \DateTime('now')) && ( date_add($dateHeureDebut, ) ) ){
//
//                }
//
//            }
//
//
//
//
//    //protected $NOM_ETAT = ['En Création','Ouverte','Fermée','En Cours', 'Terminée', 'Annulée','Historisée'];
//
//
//
//            $manager->persist($sortie);
//        }
//        $manager->flush();



        $manager->flush();
    }
}
