<?php

namespace App\Repository;

use App\BO\Filtrer;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\FiltrerType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }


    public function findForFilterForm(Filtrer $filtrer, Participant $participant)
    {

        $campus = $filtrer->getCampus();
        $nom = $filtrer->getNom();
        $dateDebut = $filtrer->getDateHeureDebut();
        $dateFin = $filtrer->getDateHeureFin();
        $isOrganisateur = $filtrer->getIsOrganisateur();
        $isInscrit = $filtrer->getIsInscrit();
        $notInscrit = $filtrer->getNotInscrit();
        $passees = $filtrer->getOldSorties();

        //dateFin can't be null if dateDebut is set, and vice versa
        //so force the null one function of the other
        if (isset($dateDebut)) {
            if ($dateFin == null) {
                $dateFin = clone $dateDebut;
                //add 7 days to dateFin
                $dateFin->add(new \DateInterval('P7D'));
                dump($dateDebut,$dateFin);
            }
        }
        if (isset($dateFin)) {
            if ($dateDebut == null) {
                //clone dateFin to initialise the dateDebut
                $dateDebut = clone $dateFin;
                //here we add 7 days to dateFin too
                $dateFin->add(new \DateInterval('P7D'));
                dump($dateDebut,$dateFin);
            }
        }

        //query builder
        $queryBuilder = $this->createQueryBuilder('sortie');
//        $queryBuilder->join('sortie.assosPartiSort', 'assos')->addSelect('assos');
//        $queryBuilder->join('assos.participant', 'participant')->addSelect('participant');
        $queryBuilder->join('sortie.campus','campus')->addSelect('campus');
        $queryBuilder->join('sortie.etat', 'etat')->addSelect('etat');

        //reduce by Campus
        if (isset($campus)) {
            $queryBuilder->andWhere('sortie.campus = :campus')->setParameter('campus', $campus);
        }

        //reduce by user string in the sortie name
        if (isset($nom) && !empty($nom)) {
            $queryBuilder->andWhere("sortie.nom LIKE :pnom")->setParameter('pnom', '%' . $nom . '%');
        }

        //reduce by date
        if (isset($dateDebut) && isset($dateFin)) {
            $queryBuilder->andWhere('sortie.dateHeureDebut BETWEEN :dateDebut AND :dateFin')
                ->setParameter('dateDebut', $dateDebut)
                ->setParameter('dateFin', $dateFin);
        }

        //reduce to sortie which the user is organisateur
        if ($isOrganisateur) {
            $queryBuilder->andWhere('sortie.organisateur = :orga')->setParameter('orga', $participant->getId());
        }

        //when the 2 options isInscrit and notInscrit are checked, the result contains all result
        if ($isInscrit xor $notInscrit) {
            $queryBuilder->join('sortie.assosPartiSort', 'assos')->addSelect('assos');
            $queryBuilder->join('assos.participant', 'participant')->addSelect('participant');

            if ($isInscrit) {
                $queryBuilder->andWhere('assos.participant = :user')->setParameter('user', $participant->getId());
            }
            if ($notInscrit) {
                $queryBuilder->andWhere('assos.participant != :user')->setParameter('user', $participant->getId());
            }
        }

        //reduce by old sorties
        if ($passees) {
            $queryBuilder->andWhere("CURRENT_DATE() > DATE_ADD(sortie.dateHeureDebut, sortie.duree, 'minute')");
        }



        //remove sorties historisées
        //$queryBuilder->join('sortie.etat', 'etat')->addSelect('etat');
        $queryBuilder->andWhere('etat != 7');
        //remove sorties en creation from the other users
//doesn't work so see below
//       $queryBuilder->andWhere('(etat != 1) and (sortie.organisateur != :userorga)')->setParameter('userorga', $participant->getId());

        //order by dateHeureSortie
        $queryBuilder->orderBy('sortie.dateHeureDebut');

        $query = $queryBuilder->getQuery();
        $result = $query->getResult();

        //remove sorties en creation from the other users
        foreach ($result as $key => $value){
            if(($value->getOrganisateur() != $participant) && ($value->getEtat()->getId() == 1)){
                unset($result[$key]);
            }
        }

        return $result;
    }
    // /**
    //  * @return Sortie[] Returns an array of Sortie objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Sortie
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
