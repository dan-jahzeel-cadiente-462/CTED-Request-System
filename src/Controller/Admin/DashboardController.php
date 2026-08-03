<?php

namespace App\Controller\Admin;

use App\Enum\Status;
use App\Repository\RequestRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'app_admin_dashboard')]
    public function index(RequestRepository $requestRepository): Response
    {
        $pendingCount = $requestRepository->countByStatus(Status::PENDING->value);
        $completedCount = $requestRepository->countByStatus(Status::COMPLETED->value);
        $cancelledCount = $requestRepository->countByStatus(Status::CANCELLED->value);
        $totalRequests = $requestRepository->count([]);

        return $this->render('admin/dashboard/index.html.twig', [
            'pendingCount' => $pendingCount,
            'completedCount' => $completedCount,
            'cancelledCount' => $cancelledCount,
            'totalRequests' => $totalRequests,
        ]);
    }
}
