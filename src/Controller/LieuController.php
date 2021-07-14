<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Ville;
use App\Form\LieuType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LieuController extends AbstractController
{
    #[Route('/lieu/creer', name: 'lieu_creer')]
    public function creer(EntityManagerInterface $entityManager,Request $request): Response
    {
        $lieu = new Lieu();

        $lieuForm = $this->createForm(LieuType::class, $lieu);

        $lieuForm->handleRequest($request);

        if ($lieuForm->isSubmitted() && $lieuForm->isValid()){

            /**
             * @var $ville Ville
             */
            $ville = $lieuForm->get('Ville')->getData();
            $lieu->setVille($ville);
            $ville->addLieux($lieu);
            $entityManager->persist($lieu);
            $entityManager->persist($ville);

            $entityManager->flush();

            $this->addFlash('success', 'Lieu ajouté avec Succès !');

            return $this->redirectToRoute('sortie_creer');
        }
        return $this->render('lieu/creer-lieu.html.twig', [
            'lieuForm' => $lieuForm->createView()
        ]);
    }

    #[Route('/lieu/ajax/select', name: 'lieu_ajax_select')]
    public function selectReactifAjax(Request $request, EntityManagerInterface $entityManager): Response
    {
        // On récupère le numero d'id correspondant a la value de l'option du select
        $valueLieuOption = json_decode($request->getContent());
        /**
         * @var $lieuSelectionne Lieu
         */
        $lieuSelectionne = $entityManager->getRepository(Lieu::class)->find($valueLieuOption->lieu_id);

        /**
         * @var $villeAssocie Ville
         */
        // On chope sa ville associée
        $villeAssocie = $lieuSelectionne->getVille();

        // On selectionne les info de ce lieu pour préremplir le formulaire
        $lieuRue =$lieuSelectionne->getRue();
        $lieuLatitude =$lieuSelectionne->getLatitude();
        $lieuLongitude =$lieuSelectionne->getLongitude();
        // On selectionne les info de sa ville associé pour préremplir le formulaire
        $villeId =$villeAssocie->getId();
        $villeCodePostal =$villeAssocie->getCodePostal();

        return $this->json([
            'codePostalAjax' => $villeCodePostal,
            'VilleId' => $villeId,
            'LieuRue' => $lieuRue,
            'LieuLatitude' => $lieuLatitude,
            'LieuLongitude' => $lieuLongitude,
        ], 200, [], ['groups' => "groupe_lieu"]);
    }

}
