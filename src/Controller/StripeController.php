<?php

namespace App\Controller;

use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Order;

class StripeController extends AbstractController
{
    #[Route('/checkout', name: 'app_checkout', methods: ['POST'])]
    public function checkout(SessionInterface $session, Request $request, LoggerInterface $logger): JsonResponse
    {
        $cart = $session->get('cart', []);

        if (empty($cart)) {
            return new JsonResponse(['error' => '❌ Le panier est vide.'], 400);
        }

        try {
            Stripe::setApiKey($this->getParameter('stripe_secret_key'));

            $lineItems = [];
            foreach ($cart as $item) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => $item['name'],
                        ],
                        'unit_amount' => $item['price'] * 100, // Prix en centimes
                    ],
                    'quantity' => $item['quantity'],
                ];
            }

            $checkoutSession = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems, // Correction : Suppression des []
                'mode' => 'payment',
                'success_url' => $this->generateUrl('app_checkout_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'cancel_url' => $this->generateUrl('app_checkout_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ]);

            return new JsonResponse(['id' => $checkoutSession->id]);

        } catch (\Exception $e) {
            $logger->error('❌ Erreur Stripe : ' . $e->getMessage());
            return new JsonResponse(['error' => 'Une erreur est survenue lors du paiement.'], 500);
        }
    }

    #[Route('/checkout/success', name: 'app_checkout_success')]
    public function success(SessionInterface $session, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $cart = $session->get('cart', []);

        if (!$user || empty($cart)) {
            return $this->redirectToRoute('app_cart');
        }

        $order = new Order();
        $order->setUser($user);
        $order->setItems($cart);
        $order->setTotal(array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $cart)));
        $order->setStatus('En cours');

        $entityManager->persist($order);
        $entityManager->flush();

        // Vider le panier après enregistrement de la commande
        $session->remove('cart');

        return $this->render('checkout/success.html.twig', ['order' => $order]);
    }

    #[Route('/checkout/cancel', name: 'app_checkout_cancel')]
    public function cancel(): Response
    {
        return $this->render('checkout/cancel.html.twig');
    }
}
