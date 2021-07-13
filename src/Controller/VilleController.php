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
        $listeLieuxAssocies = $villeSelectionne->getLieux();

//        $lieu1 =$listeLieuxAssocies[0];
//
//        foreach ($listeLieuxAssocies as $lieu) {
//
//            array_push($listeIdLieuxAssocies, $lieu);
//        }



        return $this->json([
            'codePostalAjax' => $villeSelectionne->getCodePostal(),
//            'listeLieux' => $lieu1
        ], 200, [], ['groups' => "groupe_ville"]);
    }
}
