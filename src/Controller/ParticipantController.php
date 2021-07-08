<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ParticipantController extends AbstractController
{
    #[Route('/participant', name: 'participant')]
    public function index(): Response
    {
        return $this->render('participant/index.html.twig', [
            'controller_name' => 'ParticipantController',
        ]);
    }

    #[Route('/participant/{id}', name: 'participant_editer')]
    public function editer(int $id, ParticipantRepository $participantRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $participant = $participantRepository->find($id);

        $participantFrom = $this->createForm(ParticipantType::class, $participant);

        $participantFrom->handleRequest($request);

        if($participantFrom->isSubmitted() && $participantFrom->isValid()){

            //TODO faire une fonction pour Uploader une photo de profil

            $entityManager->persist($participant);
            $entityManager->flush();

            $this->addFlash('success','Profil modifié avec Succès');

            return $this->redirectToRoute('participant_editer', [
                'id' => $participant->getId()
            ]);
        }

        return $this->render('participant/edit.html.twig', [
            'participantForm' => $participantFrom->createView()
        ]);


    }

}
