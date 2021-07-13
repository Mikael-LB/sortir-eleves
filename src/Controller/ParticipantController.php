<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Repository\CampusRepository;
use App\Repository\ParticipantRepository;
use App\Utils\UploadImage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ParticipantController extends AbstractController
{
//    #[Route('/participant', name: 'participant')]
//    public function index(): Response
//    {
//        return $this->render('participant/index.html.twig', [
//            'controller_name' => 'ParticipantController',
//        ]);
//    }

//    #[Route('/participant/{id}', name: 'participant_editer', requirements: ["id" => "\d+"])]

    #[Route('/participant/{id}', name: 'participant_editer', requirements: ["id" => "\d+"])]
    public function editer(int $id,
                           ParticipantRepository $participantRepository,
                           EntityManagerInterface $entityManager,
                           Request $request,
                           UserPasswordEncoderInterface $passwordEncoder,
                           UploadImage $uploadImage): Response
    {
//        $emailParticipantEntrant = $this->getUser()->getUsername();
//
//        $idEntrant = ($participantRepository->findOneByEmail($emailParticipantEntrant))->getId();
//        // On redirige l'utilisateur vers SA page meme s'il bidouille
//        // l'url pour voir celle de quelqu'un d'autre
//        if ($idEntrant != $id){
//
//        $url = ($this->generateUrl('participant_editer',['id'=>$idEntrant],UrlGeneratorInterface::ABSOLUTE_URL)).'/'.$idEntrant;
//            dd($this->redirectToRoute('participant_editer',['id' =>$idEntrant]));
//            return $this->redirect($url);
//        }

        $participant = $participantRepository->find($id);

        if ($participant != $this->getUser()) {
            return $this->redirectToRoute('participant_editer', ['id' => $this->getUser()->getId()]);
        }

        $participantFrom = $this->createForm(ParticipantType::class, $participant);

        $participantFrom->handleRequest($request);

        dump(2);
        if ($participantFrom->isSubmitted() && $participantFrom->isValid()) {

            //Upload image
            $urlImage = $participantFrom->get('urlImage')->getData();
            if (isset($urlImage)) {
                $dir = $this->getParameter('upload_image_profil_dir');
                $filename = $uploadImage->save($participant->getNom(), $urlImage, $dir);
                $participant->setUrlImage($filename);
            }
            //

            if ($participantFrom->get('plainPassword')->getData() !== null) {

                $encodedPassword = $passwordEncoder->encodePassword(
                    $participant,
                    $participantFrom->get('plainPassword')->getData()
                );

                $participant->setPassword($encodedPassword);
            }


            $entityManager->persist($participant);
            $entityManager->flush();

            $this->addFlash('success', 'Profil modifié avec Succès');

            dump(3);
            return $this->redirectToRoute('sortie_liste_sorties');
        }

        dump(4);
        return $this->render('participant/edit.html.twig', [
            'participantForm' => $participantFrom->createView(),
            'participant' => $participant,
        ]);


    }

    //Méthode permettant d'afficher les informations des participants
    #[Route('/participant/afficher/{id}', name: 'participant_afficher')]
    public function afficher($id,
                             ParticipantRepository $participantRepository,
                             CampusRepository $campusRepository): Response{
        //Afficher les détails concernant un participant
        $participant = $participantRepository->find($id);

        return $this->render('participant/afficher.html.twig',[
            'participant'=>$participant,

        ]);

    }

}
