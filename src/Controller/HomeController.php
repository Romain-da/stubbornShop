<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ProductRepository $productRepository): Response
    {
        // Récupérer les 3 produits mis en avant (isFeatured = true)
        $featuredProducts = $productRepository->findBy(
            ['isFeatured' => true],
            ['createdAt' => 'DESC'],
            3
        );

        return $this->render('home/index.html.twig', [
            'featured_products' => $featuredProducts,
        ]);
    }
}
