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
}
