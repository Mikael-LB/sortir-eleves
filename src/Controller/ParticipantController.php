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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ParticipantController extends AbstractController
{
    #[Route('/participant', name: 'participant')]
    public function index(): Response
    {
        return $this->render('participant/index.html.twig', [
            'controller_name' => 'ParticipantController',
        ]);
    }

    #[Route('/participant/{id}', name: 'participant_editer', requirements: ["id" => "\d+"])]
    public function editer(int $id=1, ParticipantRepository $participantRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $emailParticipantEntrant = $this->getUser()->getUsername();

        $idEntrant = ($participantRepository->findOneByEmail($emailParticipantEntrant))->getId();
        // On redirige l'utilisateur vers SA page meme s'il bidouille
        // l'url pour voir celle de quelqu'un d'autre
        if ($idEntrant != $id){
        $url = ($this->generateUrl('participant_editer',['id'=>$idEntrant],UrlGeneratorInterface::ABSOLUTE_URL)).'/'.$idEntrant;
            return $this->redirect($url);
        }

        $participant = $participantRepository->find($id);

        $participantFrom = $this->createForm(ParticipantType::class, $participant);

        $participantFrom->handleRequest($request);

        if($participantFrom->isSubmitted() && $participantFrom->isValid()){

            //TODO faire une fonction pour Uploader une photo de profil

            $entityManager->persist($participant);
            $entityManager->flush();

            $this->addFlash('success','Profil modifié avec Succès');

            return $this->redirectToRoute('sortie_liste_sorties');
        }

        return $this->render('participant/edit.html.twig', [
            'participantForm' => $participantFrom->createView()
        ]);


    }

}
