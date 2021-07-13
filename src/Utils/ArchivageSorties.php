<?php


namespace App\Utils;


use App\Entity\Etat;
use App\Entity\Sortie;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;

class ArchivageSorties
{

    private $NOM_CAMPUS = ['Rennes','Quimper','Niort','Nantes'];
    private $NOM_ETAT = ['En Création','Ouverte','Clôturée','En Cours', 'Terminée', 'Annulée','Historisée'];
    /**
     * @var $sortieRepository SortieRepository
     */
    private $sortieRepository;
    /**
     * @var $etatRepository EtatRepository
     */
    private $etatRepository;
    /**
     * @var $entityManager EntityManagerInterface
     */
    private $entityManager;

    public function __construct( SortieRepository $sortieRepository,
                                EtatRepository $etatRepository,
                                EntityManagerInterface $entityManager
    ){
        $this->sortieRepository = $sortieRepository;
        $this->etatRepository = $etatRepository;
        $this->entityManager = $entityManager;
    }

    public function archiver() : self{
        $sortieListe = $this->sortieRepository->findAll();

        $etatListe = $this->etatRepository->findAll();
        /**
         * @var $etatListe Etat[]
         *
         */
        $nowDate = new \DateTime('now');

        $dateTimeHistorisation = new \DateTime($nowDate->format('Y-m-d H:i:s'));
        date_modify($dateTimeHistorisation, '-1 month');


        foreach ($sortieListe as $sortie){
            /**
             * @var $sortie Sortie
             */
            $dateTimeDebut = $sortie->getDateHeureDebut();
            $dateFinInscr = $sortie->getDateLimiteInscription();
            $duree = $sortie->getDuree();
            // Calcul de la DateTime de fin de l'activité
            $dateTimeFin = new \DateTime($dateTimeDebut->format('Y-m-d H:i:s'));
            date_modify($dateTimeFin, '+'.($duree * 60).' seconds');
            $etatPrecedent = $sortie->getEtat();

            // on classe les sorties qui ne sont pas en cours de créations
            if ($sortie->getEtat()->getLibelle() != $etatListe[0]) {


                // HISTORISEE
                if ($dateTimeFin > $dateTimeHistorisation) {
                    $sortie->setEtat($etatListe[6]);
                    $etatPrecedent->removeSorty($sortie);
                    $etatListe[6]->addSorty($sortie);
                    $this->entityManager->persist($etatPrecedent);
                    $this->entityManager->persist($etatListe[6]);
                }

                if ($sortie->getEtat()->getLibelle() != $etatListe[5]){

                    // TERMINER pour celles qui ne sont pas Annulée
                    if ( (($dateTimeFin < $nowDate) && ($dateTimeFin < $dateTimeHistorisation)) ) {
                        $sortie->setEtat($etatListe[4]);
                        $etatPrecedent->removeSorty($sortie);
                        $etatListe[4]->addSorty($sortie);
                        $this->entityManager->persist($etatPrecedent);
                        $this->entityManager->persist($etatListe[4]);
                    }

                    // EN COURS pour celles qui ne sont pas Annulée
                    if ( (($dateTimeDebut < $nowDate) && ($dateTimeFin > $nowDate)) ){
                        $sortie->setEtat($etatListe[3]);
                        $etatPrecedent->removeSorty($sortie);
                        $etatListe[3]->addSorty($sortie);
                        $this->entityManager->persist($etatPrecedent);
                        $this->entityManager->persist($etatListe[3]);
                    }

                    // CLOTUREE pour celles qui ne sont pas Annulée
                    if ( (($dateFinInscr < $nowDate) && ($dateTimeDebut > $nowDate)) ){
                        $sortie->setEtat($etatListe[2]);
                        $etatPrecedent->removeSorty($sortie);
                        $etatListe[2]->addSorty($sortie);
                        $this->entityManager->persist($etatPrecedent);
                        $this->entityManager->persist($etatListe[2]);
                    }

                    if ( ($dateFinInscr > $nowDate) ){
                        $sortie->setEtat($etatListe[1]);
                        $etatPrecedent->removeSorty($sortie);
                        $etatListe[1]->addSorty($sortie);
                        $this->entityManager->persist($etatPrecedent);
                        $this->entityManager->persist($etatListe[1]);
                    }

                }
                $this->entityManager->persist($sortie);
            }
        }
                $this->entityManager->flush();

        return $this;

    }

}