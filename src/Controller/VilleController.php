<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Ville;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VilleController extends AbstractController
{
    #[Route('/ville/ajax/select', name: 'ville_ajax_select')]
    public function selectReactifAjax(Request $request, EntityManagerInterface $entityManager): Response
    {
        // On récupère le numero d'id correspondant a la value de l'option du select
        $valueVilleOption = json_decode($request->getContent());
        /**
         * @var $villeSelectionne Ville
         */
        $villeSelectionne = $entityManager->getRepository(Ville::class)->find($valueVilleOption->ville_id);

        /**
         * @var $listeLieuxAssocies Lieu[]
         */
        // On chope tous ses lieux associées
        $listeLieuxAssocies = $villeSelectionne->getLieux();

        // On selectionne les info du 1er lieu associé pour préremplir le formulaire
        $lieu1Id =$listeLieuxAssocies[0]->getId();
        $lieu1Rue =$listeLieuxAssocies[0]->getRue();
        $lieu1Latitude =$listeLieuxAssocies[0]->getLatitude();
        $lieu1Longitude =$listeLieuxAssocies[0]->getLongitude();

        return $this->json([
            'codePostalAjax' => $villeSelectionne->getCodePostal(),
            'LieuId' => $lieu1Id,
            'LieuRue' => $lieu1Rue,
            'LieuLatitude' => $lieu1Latitude,
            'LieuLongitude' => $lieu1Longitude,
        ], 200, [], ['groups' => "groupe_ville"]);
    }
}
