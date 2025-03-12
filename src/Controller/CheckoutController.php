<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;
use Stripe\Checkout\Session;
use Stripe\StripeClient;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CheckoutController extends AbstractController
{
    #[Route('/checkout', name: 'app_checkout', methods: ['POST'])]
    public function checkout(Request $request, LoggerInterface $logger, StripeClient $stripeClient): JsonResponse
    {
        // 🛠 Débogage : Vérifie si la clé Stripe est bien récupérée
        $stripeSecretKey = $this->getParameter('stripe_secret_key');
        dump($stripeSecretKey); die(); // 🔹 Vérifie si la clé est correcte

        if (!$stripeSecretKey) {
            return new JsonResponse(['error' => 'Clé Stripe non définie !'], 500);
        }

        // 🔹 Vérifie si le panier est bien envoyé
        $data = json_decode($request->getContent(), true);
        dump($data); die(); // 🔹 Voir le contenu de la requête

        if (!$data || !isset($data['cart'])) {
            return new JsonResponse(['error' => 'Panier vide ou mal formaté !'], 400);
        }

        try {
            $cartItems = [];
            foreach ($data['cart'] as $item) {
                $cartItems[] = [
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => ['name' => $item['name']],
                        'unit_amount' => intval($item['price'] * 100), // Convertir en centimes
                    ],
                    'quantity' => intval($item['quantity']),
                ];
            }

            // Création de la session Stripe
            $session = $stripeClient->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'line_items' => $cartItems,
                'mode' => 'payment',
                'success_url' => $this->generateUrl('app_checkout_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'cancel_url' => $this->generateUrl('app_checkout_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ]);

            return new JsonResponse(['id' => $session->id]);

        } catch (\Exception $e) {
            $logger->error('Erreur Stripe : ' . $e->getMessage());
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
