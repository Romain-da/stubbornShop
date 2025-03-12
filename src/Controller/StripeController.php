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

class StripeController extends AbstractController
{
    #[Route('/checkout', name: 'app_checkout', methods: ['POST'])]
    public function checkout(SessionInterface $session, Request $request): JsonResponse
    {
        $cart = $session->get('cart', []);

        if (empty($cart)) {
            return $this->redirectToRoute('app_cart');
        }

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
            'line_items' => [$lineItems],
            'mode' => 'payment',
            'success_url' => $this->generateUrl('app_checkout_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->generateUrl('app_checkout_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        return new JsonResponse(['id' => $checkoutSession->id]);
    }

    #[Route('/checkout/success', name: 'app_checkout_success')]
    public function success(SessionInterface $session): Response
    {
        // Vider le panier après paiement
        $session->set('cart', []);

        return $this->render('cart/success.html.twig');
    }

    #[Route('/checkout/cancel', name: 'app_checkout_cancel')]
    public function cancel(): Response
    {
        return $this->render('cart/cancel.html.twig');
    }
}
