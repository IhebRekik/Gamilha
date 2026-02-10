<?php

namespace App\Controller;

use App\Entity\CoachingVideo;
use App\Form\CoachingVideoType;
use App\Repository\CoachingVideoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/video')]
class CoachingVideoController extends AbstractController
{
    #[Route('/', name: 'video_index')]
    public function index(CoachingVideoRepository $repository): Response
    {
        return $this->render('coachingvideo/index.html.twig', [
            'videos' => $repository->findAll(),
        ]);
    }

    #[Route('/new', name: 'video_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $video = new CoachingVideo();
        $form = $this->createForm(CoachingVideoType::class, $video);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $uploadedFile = $form->get('videoFile')->getData();

            if ($uploadedFile) {
                $fileName = uniqid().'.'.$uploadedFile->guessExtension();
                $uploadedFile->move(
                    $this->getParameter('videos_directory'),
                    $fileName
                );
                $video->setUrl('uploads/videos/'.$fileName);
            }

            $em->persist($video);
            $em->flush();

            return $this->redirectToRoute('video_index');
        }

        return $this->render('coachingvideo/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'video_edit')]
    public function edit(
        CoachingVideo $video,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(CoachingVideoType::class, $video);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $uploadedFile = $form->get('videoFile')->getData();

            if ($uploadedFile) {
                $fileName = uniqid().'.'.$uploadedFile->guessExtension();
                $uploadedFile->move(
                    $this->getParameter('videos_directory'),
                    $fileName
                );
                $video->setUrl('uploads/videos/'.$fileName);
            }

            $em->flush();
            return $this->redirectToRoute('video_index');
        }

        return $this->render('coachingvideo/edit.html.twig', [
            'form' => $form->createView(),
            'video' => $video,
        ]);
    }

    #[Route('/{id}/delete', name: 'video_delete', methods: ['POST'])]
    public function delete(
        CoachingVideo $video,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$video->getId(), $request->request->get('_token'))) {
            $em->remove($video);
            $em->flush();
        }

        return $this->redirectToRoute('video_index');
    }
}
