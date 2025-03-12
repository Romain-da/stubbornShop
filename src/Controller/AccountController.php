<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/account')]
#[IsGranted('ROLE_USER')] // Seuls les utilisateurs connectés peuvent accéder
class AccountController extends AbstractController
{
    #[Route('/', name: 'app_account')]
    public function index(OrderRepository $orderRepository): Response
    {
        // Récupère l'utilisateur connecté
        $user = $this->getUser();
        
        // Récupère les commandes de l'utilisateur
        $orders = $orderRepository->findBy(['user' => $user], ['createdAt' => 'DESC']);

        return $this->render('account/index.html.twig', [
            'user' => $user,
            'orders' => $orders,
        ]);
    }
}
