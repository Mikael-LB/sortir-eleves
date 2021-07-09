<?php

namespace App\Repository;

use App\BO\Filtrer;
use App\Entity\Sortie;
use App\Form\FiltrerType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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


    public function findForFilterForm(Filtrer $filtrer){

        $idCampus = $filtrer->getCampus()->getId();
        $nom = $filtrer->getNom();
        $dateDebut = $filtrer->getDateHeureDebut();
        $dateFin = $filtrer->getDateHeureFin();
        $isOrganisateur = $filtrer->getIsOrganisateur();

        $queryBuilder = $this->createQueryBuilder('sortie');
        $queryBuilder->andWhere('sortie.campus = :idcampus')->setParameter('idcampus',$idCampus);
        if (isset($nom) && !empty($nom)){
            $queryBuilder->andWhere("sortie.nom LIKE :pnom")->setParameter('pnom','%'.$nom.'%');
        }
        if(isset($dateDebut) && !empty($dateDebut)){
            $queryBuilder->andWhere('sortie.dateHeureDebut BETWEEN :dateDebut AND :dateFin')
                ->setParameter('dateDebut',$dateDebut)
                ->setParameter('dateFin',$dateFin);
        }


        $query = $queryBuilder->getQuery();
        $result = $query->getResult();

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
