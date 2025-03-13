<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\OrderRepository;
use App\Entity\Order;

#[Route('/account/orders')]
class OrderController extends AbstractController
{
    #[Route('/', name: 'app_orders')]
    public function orders(OrderRepository $orderRepository): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login'); // Redirige si l'utilisateur n'est pas connecté
        }

        $orders = $orderRepository->findBy(['user' => $this->getUser()], ['createdAt' => 'DESC']);

        return $this->render('account/orders.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/{id}', name: 'app_order_detail', requirements: ['id' => '\d+'])]
    public function orderDetail(Order $order): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        if ($order->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Vous n'avez pas accès à cette commande.");
        }

        return $this->render('account/order_detail.html.twig', [
            'order' => $order,
        ]);
    }
}
