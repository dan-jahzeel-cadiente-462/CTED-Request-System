<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

final class QueueController extends AbstractController
{
    #[Route('/admin/queue', name: 'app_admin_queue')]
    public function index(): RedirectResponse
    {
        return $this->redirectToRoute('app_admin_requests');
    }
}
