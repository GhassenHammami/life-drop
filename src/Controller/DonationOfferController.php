<?php

namespace App\Controller;

use App\Entity\DonationOffer;
use App\Form\DonationOfferType;
use App\Repository\DonationOfferRepository;
use App\Repository\BloodRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('offers')]
final class DonationOfferController extends AbstractController
{
    #[Route(name: 'app_donation_offer_index', methods: ['GET'])]
    public function index(DonationOfferRepository $donationOfferRepository): Response
    {
        return $this->render('donation_offer/index.html.twig', [
            'donation_offers' => $donationOfferRepository->findAll(),
        ]);
    }

    #[Route('/mine', name: 'app_donation_offer_mine', methods: ['GET'])]
    public function mine(DonationOfferRepository $repo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $offers = $repo->findBy(
            ['donor' => $this->getUser()],
            ['createdAt' => 'DESC']
        );

        return $this->render('donation_offer/mine.html.twig', [
            'offers' => $offers,
        ]);
    }

    #[Route('/request/{id}/new', name: 'app_donation_offer_new', methods: ['GET', 'POST'])]
    public function new(int $id, BloodRequestRepository $bloodRepo, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $bloodRequest = $bloodRepo->find($id);
        if (!$bloodRequest) {
            $this->addFlash('error', 'Blood request not found.');
            return $this->redirectToRoute('app_blood_request_index');
        }

        if (($bloodRequest->getStatus() ?? '') === 'CLOSED') {
            $this->addFlash('error', 'This request is closed. You can’t make a donation offer anymore.');
            return $this->redirectToRoute('app_blood_request_show', ['id' => $bloodRequest->getId()]);
        }

        if ($bloodRequest->getCreatedBy() === $this->getUser()) {
            $this->addFlash('error', 'You cannot offer to your own request.');
            return $this->redirectToRoute('app_blood_request_show', ['id' => $bloodRequest->getId()]);
        }

        foreach ($bloodRequest->getDonationOffers() as $existing) {
            if ($existing->getDonor() === $this->getUser() && in_array($existing->getStatus(), ['PENDING', 'ACCEPTED'], true)) {
                $this->addFlash('error', 'You already have an active offer for this request.');
                return $this->redirectToRoute('app_blood_request_show', ['id' => $bloodRequest->getId()]);
            }
        }

        $offer = new DonationOffer();
        $offer->setRequest($bloodRequest);
        $offer->setDonor($this->getUser());

        $form = $this->createForm(DonationOfferType::class, $offer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($offer);
            $em->flush();

            $this->addFlash('success', 'Offer sent. The requester will see it.');
            return $this->redirectToRoute('app_blood_request_show', ['id' => $bloodRequest->getId()]);
        }

        return $this->render('donation_offer/new.html.twig', [
            'form' => $form,
            'blood_request' => $bloodRequest,
        ]);
    }

    #[Route('/{id}/accept', name: 'app_donation_offer_accept', methods: ['POST'])]
    public function accept(int $id, DonationOfferRepository $offerRepo, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $offer = $offerRepo->find($id);
        if (!$offer) {
            $this->addFlash('error', 'Donation offer not found.');
            return $this->redirectToRoute('app_blood_request_index');
        }

        $req = $offer->getRequest();
        if (!$req) {
            $this->addFlash('error', 'This offer is missing its request.');
            return $this->redirectToRoute('app_blood_request_index');
        }

        if (($req->getStatus() ?? '') === 'CLOSED') {
            $this->addFlash('error', 'This request is closed. You can’t accept offers anymore.');
            return $this->redirectToRoute('app_blood_request_show', ['id' => $req->getId()]);
        }

        if ($req->getCreatedBy() !== $this->getUser()) {
            $this->addFlash('error', 'You are not allowed to accept offers for this request.');
            return $this->redirectToRoute('app_blood_request_show', ['id' => $req->getId()]);
        }

        if (!$this->isCsrfTokenValid('offer_accept' . $offer->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('app_blood_request_show', ['id' => $req->getId()]);
        }

        if (($offer->getStatus() ?? '') !== 'PENDING') {
            $this->addFlash('error', 'You can only accept a pending offer.');
            return $this->redirectToRoute('app_blood_request_show', ['id' => $req->getId()]);
        }

        $offer->setStatus('ACCEPTED');
        $req->setStatus('IN_PROGRESS');
        $req->setUpdatedAt(new \DateTimeImmutable());

        $em->flush();

        $this->addFlash('success', 'Offer accepted.');
        return $this->redirectToRoute('app_blood_request_show', ['id' => $req->getId()]);
    }

    #[Route('/{id}/reject', name: 'app_donation_offer_reject', methods: ['POST'])]
    public function reject(int $id, DonationOfferRepository $offerRepo, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $offer = $offerRepo->find($id);
        if (!$offer) {
            $this->addFlash('error', 'Donation offer not found.');
            return $this->redirectToRoute('app_blood_request_index');
        }

        $req = $offer->getRequest();
        if (!$req) {
            $this->addFlash('error', 'This offer is missing its request.');
            return $this->redirectToRoute('app_blood_request_index');
        }

        if (($req->getStatus() ?? '') === 'CLOSED') {
            $this->addFlash('error', 'This request is closed. You can’t reject offers anymore.');
            return $this->redirectToRoute('app_blood_request_show', ['id' => $req->getId()]);
        }

        if ($req->getCreatedBy() !== $this->getUser()) {
            $this->addFlash('error', 'You are not allowed to reject offers for this request.');
            return $this->redirectToRoute('app_blood_request_show', ['id' => $req->getId()]);
        }

        if (!$this->isCsrfTokenValid('offer_reject' . $offer->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('app_blood_request_show', ['id' => $req->getId()]);
        }

        if (($offer->getStatus() ?? '') !== 'PENDING') {
            $this->addFlash('error', 'You can only reject a pending offer.');
            return $this->redirectToRoute('app_blood_request_show', ['id' => $req->getId()]);
        }

        $offer->setStatus('REJECTED');
        $req->setUpdatedAt(new \DateTimeImmutable());

        $em->flush();

        $this->addFlash('success', 'Offer rejected.');
        return $this->redirectToRoute('app_blood_request_show', ['id' => $req->getId()]);
    }

    #[Route('/{id}/withdraw', name: 'app_donation_offer_withdraw', methods: ['POST'])]
    public function withdraw(int $id, DonationOfferRepository $offerRepo, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $offer = $offerRepo->find($id);
        if (!$offer) {
            $this->addFlash('error', 'Donation offer not found.');
            return $this->redirectToRoute('app_blood_request_index');
        }

        $req = $offer->getRequest();
        if (!$req) {
            $this->addFlash('error', 'This offer is missing its request.');
            return $this->redirectToRoute('app_blood_request_index');
        }

        if (($req->getStatus() ?? '') === 'CLOSED') {
            $this->addFlash('error', 'This request is closed. You can’t withdraw offers anymore.');
            return $this->redirectToRoute('app_blood_request_show', ['id' => $req->getId()]);
        }

        if ($offer->getDonor() !== $this->getUser()) {
            $this->addFlash('error', 'You are not allowed to withdraw this offer.');
            return $this->redirectToRoute('app_blood_request_show', ['id' => $req->getId()]);
        }

        if (!$this->isCsrfTokenValid('offer_withdraw' . $offer->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('app_blood_request_show', ['id' => $req->getId()]);
        }

        if (($offer->getStatus() ?? '') !== 'PENDING') {
            $this->addFlash('error', 'You can only withdraw a pending offer.');
            return $this->redirectToRoute('app_blood_request_show', ['id' => $req->getId()]);
        }

        $offer->setStatus('WITHDRAWN');
        $req->setUpdatedAt(new \DateTimeImmutable());

        if ($req->getStatus() === 'IN_PROGRESS') {
            $hasOtherAccepted = false;
            foreach ($req->getDonationOffers() as $o) {
                if ($o !== $offer && $o->getStatus() === 'ACCEPTED') {
                    $hasOtherAccepted = true;
                    break;
                }
            }
            if (!$hasOtherAccepted) {
                $req->setStatus('OPEN');
            }
        }

        $em->flush();

        $this->addFlash('success', 'Offer withdrawn.');
        return $this->redirectToRoute('app_blood_request_show', ['id' => $req->getId()]);
    }

    #[Route('/{id}/message', name: 'app_donation_offer_update_message', methods: ['POST'])]
    public function updateMessage(int $id, DonationOfferRepository $offerRepo, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $offer = $offerRepo->find($id);
        if (!$offer) {
            $this->addFlash('error', 'Donation offer not found.');
            return $this->redirectToRoute('app_blood_request_index');
        }

        $req = $offer->getRequest();
        if (!$req) {
            $this->addFlash('error', 'This offer is missing its request.');
            return $this->redirectToRoute('app_blood_request_index');
        }

        if (($req->getStatus() ?? '') === 'CLOSED') {
            $this->addFlash('error', 'This request is closed. You can’t edit offers anymore.');
            return $this->redirectToRoute('app_blood_request_show', ['id' => $req->getId()]);
        }

        if ($offer->getDonor() !== $this->getUser()) {
            $this->addFlash('error', 'You are not allowed to edit this offer.');
            return $this->redirectToRoute('app_blood_request_show', ['id' => $req->getId()]);
        }

        if (($offer->getStatus() ?? '') !== 'PENDING') {
            $this->addFlash('error', 'You can only edit a pending offer.');
            return $this->redirectToRoute('app_blood_request_show', ['id' => $req->getId()]);
        }

        if (!$this->isCsrfTokenValid('offer_update_message' . $offer->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('app_blood_request_show', ['id' => $req->getId()]);
        }

        $message = trim((string) $request->request->get('message', ''));
        if ($message === '') {
            $this->addFlash('error', 'Message cannot be empty.');
            return $this->redirectToRoute('app_blood_request_show', ['id' => $req->getId()]);
        }

        $offer->setMessage($message);
        $req->setUpdatedAt(new \DateTimeImmutable());

        $em->flush();

        $this->addFlash('success', 'Offer message updated.');
        return $this->redirectToRoute('app_blood_request_show', ['id' => $req->getId()]);
    }
}
