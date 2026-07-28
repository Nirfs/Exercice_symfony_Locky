<?php

namespace App\Controller;

use App\Entity\Prestation;
use App\Form\PrestationType;
use App\Repository\PrestationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class PrestationController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(
        PrestationRepository $prestationRepository,
        UserRepository $userRepository,
    ): Response {
        return $this->render('prestation/index.html.twig', [
            'prestations' => $prestationRepository->findLatest(10),
            'dernierUtilisateur' => $userRepository->findLastRegistered(),
        ]);
    }

    #[Route('/prestation/{id}', name: 'app_prestation_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Prestation $prestation): Response
    {
        return $this->render('prestation/show.html.twig', [
            'prestation' => $prestation,
        ]);
    }

    #[Route('/prestation/nouvelle', name: 'app_prestation_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $prestation = new Prestation();
        $prestation->setDateCreation(new \DateTime());

        $form = $this->createForm(PrestationType::class, $prestation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($prestation);
            $entityManager->flush();

            $this->addFlash('success', 'La prestation a bien été ajoutée.');

            return $this->redirectToRoute('app_prestation_show', ['id' => $prestation->getId()]);
        }

        return $this->render('prestation/new.html.twig', [
            'form' => $form,
        ]);
    }
}
