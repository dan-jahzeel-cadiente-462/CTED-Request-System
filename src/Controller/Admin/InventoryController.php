<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class InventoryController extends AbstractController
{
    #[Route('/admin/inventory', name: 'app_admin_inventory')]
    public function index(): Response
    {
        return $this->render('admin/inventory/index.html.twig', [
            'controller_name' => 'InventoryController',
        ]);
    }
}
