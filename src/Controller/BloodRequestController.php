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
        $criteria = [];

        $status = strtoupper((string) $request->query->get('status', 'OPEN'));
        if (in_array($status, ['OPEN', 'CLOSED'], true)) {
            $criteria['status'] = $status;
        }

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

    #[Route('/mine', name: 'app_blood_request_mine', methods: ['GET'])]
    public function mine(BloodRequestRepository $repo): Response
    {

        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $requests = $repo->findBy(
            ['createdBy' => $user],
            ['createdAt' => 'DESC']
        );

        return $this->render('blood_request/mine.html.twig', [
            'requests' => $requests,
        ]);
    }

    #[Route('/{id}', name: 'app_blood_request_show', methods: ['GET'])]
    public function show(int $id, BloodRequestRepository $repo): Response
    {
        $bloodRequest = $repo->find($id);

        if (!$bloodRequest) {
            $this->addFlash('error', 'Blood request not found.');
            return $this->redirectToRoute('app_blood_request_index');
        }

        return $this->render('blood_request/show.html.twig', [
            'blood_request' => $bloodRequest,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_blood_request_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, BloodRequestRepository $repo, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $bloodRequest = $repo->find($id);
        if (!$bloodRequest) {
            $this->addFlash('error', 'Blood request not found.');
            return $this->redirectToRoute('app_blood_request_index');
        }

        if ($bloodRequest->getCreatedBy() !== $this->getUser()) {
            $this->addFlash('error', 'You are not allowed to edit this request.');
            return $this->redirectToRoute('app_blood_request_show', ['id' => $bloodRequest->getId()]);
        }

        if (($bloodRequest->getStatus() ?? '') === 'CLOSED') {
            $this->addFlash('error', 'This request is closed and cannot be edited.');
            return $this->redirectToRoute('app_blood_request_show', ['id' => $bloodRequest->getId()]);
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
    public function delete(int $id, Request $request, BloodRequestRepository $repo, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $bloodRequest = $repo->find($id);
        if (!$bloodRequest) {
            $this->addFlash('error', 'Blood request not found.');
            return $this->redirectToRoute('app_blood_request_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($bloodRequest->getCreatedBy() !== $this->getUser()) {
            $this->addFlash('error', 'You are not allowed to delete this request.');
            return $this->redirectToRoute('app_blood_request_show', ['id' => $bloodRequest->getId()], Response::HTTP_SEE_OTHER);
        }

        if (($bloodRequest->getStatus() ?? '') === 'CLOSED') {
            $this->addFlash('error', 'This request is closed and cannot be deleted.');
            return $this->redirectToRoute('app_blood_request_show', ['id' => $bloodRequest->getId()], Response::HTTP_SEE_OTHER);
        }

        // Use request->request for standard form POST
        if ($this->isCsrfTokenValid('delete' . $bloodRequest->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($bloodRequest);
            $entityManager->flush();
            $this->addFlash('success', 'Blood request deleted successfully.');
        } else {
            $this->addFlash('error', 'Invalid CSRF token. Please try again.');
        }

        return $this->redirectToRoute('app_blood_request_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/blood-request/{id}/close', name: 'app_blood_request_close', methods: ['POST'])]
    public function close(int $id, Request $request, BloodRequestRepository $repo, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $bloodRequest = $repo->find($id);
        if (!$bloodRequest) {
            $this->addFlash('error', 'Blood request not found.');
            return $this->redirectToRoute('app_blood_request_index');
        }

        if ($bloodRequest->getCreatedBy() !== $this->getUser()) {
            $this->addFlash('error', 'You are not allowed to close this request.');
            return $this->redirectToRoute('app_blood_request_show', ['id' => $bloodRequest->getId()]);
        }

        if (!$this->isCsrfTokenValid('close' . $bloodRequest->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('app_blood_request_show', ['id' => $bloodRequest->getId()]);
        }

        if (($bloodRequest->getStatus() ?? '') === 'CLOSED') {
            $this->addFlash('error', 'This request is already closed.');
            return $this->redirectToRoute('app_blood_request_show', ['id' => $bloodRequest->getId()]);
        }

        $bloodRequest->setStatus('CLOSED');
        $bloodRequest->setUpdatedAt(new \DateTimeImmutable());

        foreach ($bloodRequest->getDonationOffers() as $offer) {
            if (($offer->getStatus() ?? '') === 'PENDING') {
                $offer->setStatus('EXPIRED');
            }
        }

        $em->flush();

        $this->addFlash('success', 'Request closed.');
        return $this->redirectToRoute('app_blood_request_show', ['id' => $bloodRequest->getId()]);
    }

}
