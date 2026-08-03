<?php

namespace App\Controller\Admin;

use App\Entity\Request as RequestEntity;
use App\Entity\RequestStatus;
use App\Enum\Status;
use App\Repository\RequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RequestsController extends AbstractController
{
    #[Route('/admin/requests', name: 'app_admin_requests')]
    public function index(RequestRepository $requestRepository, Request $request): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = 12;
        $totalRequests = $requestRepository->count([]);
        $totalPages = (int) ceil($totalRequests / $limit);
        $requests = $requestRepository->findRequestsPage($page, $limit);

        return $this->render('admin/requests/index.html.twig', [
            'requests' => $requests,
            'currentPage' => $page,
            'totalPages' => max(1, $totalPages),
            'totalRequests' => $totalRequests,
        ]);
    }

    #[Route('/admin/requests/{id}', name: 'app_admin_requests_show', methods: ['GET'])]
    public function show(RequestEntity $requestEntity, Request $request): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));

        return $this->render('admin/requests/show.html.twig', [
            'requestEntity' => $requestEntity,
            'currentPage' => $page,
            'statusChoices' => Status::ALL,
        ]);
    }

    #[Route('/admin/requests/{id}/status', name: 'app_admin_requests_status', methods: ['POST'])]
    public function updateStatus(Request $request, RequestEntity $requestEntity, EntityManagerInterface $em): Response
    {
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('status-request'.$requestEntity->getId(), $token)) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $status = $request->request->get('status');
        $note = $request->request->get('note');
        $processedBy = $request->request->get('processedBy', 'Admin');
        $page = max(1, (int) $request->request->get('page', 1));

        if (!in_array($status, Status::ALL, true)) {
            $this->addFlash('error', 'Invalid status selection.');

            return $this->redirectToRoute('app_admin_requests_show', ['id' => $requestEntity->getId(), 'page' => $page]);
        }

        $requestEntity->setStatus($status);

        $requestStatus = new RequestStatus();
        $requestStatus->setRequest($requestEntity);
        $requestStatus->setStatus($status);
        $requestStatus->setNote($note);
        $requestStatus->setProcessedBy($processedBy);

        $em->persist($requestStatus);
        $em->flush();

        $this->addFlash('success', 'Request status updated.');

        return $this->redirectToRoute('app_admin_requests_show', ['id' => $requestEntity->getId(), 'page' => $page]);
    }

    #[Route('/admin/requests/{id}/delete', name: 'app_admin_requests_delete', methods: ['POST'])]
    public function delete(Request $request, RequestEntity $requestEntity, EntityManagerInterface $em): Response
    {
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete-request'.$requestEntity->getId(), $token)) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $em->remove($requestEntity);
        $em->flush();

        $this->addFlash('success', 'Request deleted successfully.');

        $page = max(1, (int) $request->request->get('page', 1));

        return $this->redirectToRoute('app_admin_requests', ['page' => $page]);
    }
}
