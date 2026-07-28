<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class QueueController extends AbstractController
{
    #[Route('/admin/queue', name: 'app_admin_queue')]
    public function index(): Response
    {
        return $this->render('admin/queue/index.html.twig', [
            'controller_name' => 'QueueController',
        ]);
    }
}
