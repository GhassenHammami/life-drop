<?php

namespace App\Controller;

use App\Entity\BloodRequest;
use App\Form\BloodRequestType;
use App\Repository\BloodRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Domain\Lists;

#[Route('/requests')]
final class BloodRequestController extends AbstractController
{
    #[Route(name: 'app_blood_request_index', methods: ['GET'])]
    public function index(Request $request, BloodRequestRepository $repo): Response
    {
        $criteria = ['status' => 'OPEN'];

        if ($city = $request->query->get('city')) {
            $criteria['city'] = $city;
        }
        if ($bloodType = $request->query->get('bloodType')) {
            $criteria['bloodType'] = $bloodType;
        }
        if ($urgency = $request->query->get('urgency')) {
            $criteria['urgency'] = $urgency;
        }

        return $this->render('blood_request/index.html.twig', [
            'blood_requests' => $repo->findBy($criteria, ['createdAt' => 'DESC']),
            'cities' => Lists::TUNISIA_CITIES,
            'blood_types' => Lists::BLOOD_TYPES,
            'urgencies' => Lists::URGENCIES,
        ]);
    }

    #[Route('/new', name: 'app_blood_request_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $bloodRequest = new BloodRequest();
        $form = $this->createForm(BloodRequestType::class, $bloodRequest);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $bloodRequest->setCreatedAt(new \DateTimeImmutable());
            $bloodRequest->setCreatedBy($this->getUser());
            $em->persist($bloodRequest);
            $em->flush();

            $this->addFlash('success', 'Blood request published successfully.');
            return $this->redirectToRoute('app_blood_request_index');
        }

        return $this->render('blood_request/new.html.twig', [
            'form' => $form,
            'hospitalsByCity' => Lists::HOSPITALS_BY_CITY,
        ]);
    }


    #[Route('/{id}', name: 'app_blood_request_show', methods: ['GET'])]
    public function show(BloodRequest $bloodRequest): Response
    {
        return $this->render('blood_request/show.html.twig', [
            'blood_request' => $bloodRequest,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_blood_request_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, BloodRequest $bloodRequest, EntityManagerInterface $entityManager): Response
    {

        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($bloodRequest->getCreatedBy() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(BloodRequestType::class, $bloodRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $bloodRequest->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            $this->addFlash('success', 'Blood request updated successfully.');
            return $this->redirectToRoute('app_blood_request_show', ['id' => $bloodRequest->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('blood_request/edit.html.twig', [
            'blood_request' => $bloodRequest,
            'form' => $form,
            'hospitalsByCity' => Lists::HOSPITALS_BY_CITY,
        ]);
    }

    #[Route('/{id}', name: 'app_blood_request_delete', methods: ['POST'])]
    public function delete(Request $request, BloodRequest $bloodRequest, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($bloodRequest->getCreatedBy() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete' . $bloodRequest->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($bloodRequest);
            $entityManager->flush();

            $this->addFlash('success', 'Blood request deleted successfully.');
        } else {
            $this->addFlash('error', 'Invalid CSRF token. Please try again.');
        }

        return $this->redirectToRoute('app_blood_request_index', [], Response::HTTP_SEE_OTHER);

    }
}
