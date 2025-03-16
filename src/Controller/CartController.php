<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

#[Route('/cart')]
class CartController extends AbstractController
{
    #[Route('/', name: 'app_cart')]
    public function index(SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);
        
        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
            'stripe_public_key' => $this->getParameter('stripe_public_key')
        ]);
    }

    #[Route('/add/{id}', name: 'app_cart_add', methods: ['POST', 'GET'])]
    public function addToCart(int $id, Request $request, SessionInterface $session, ProductRepository $productRepository): Response
    {
        $cart = $session->get('cart', []);

        $product = $productRepository->find($id);
        if (!$product) {
            throw $this->createNotFoundException("Le produit n'existe pas.");
        }

        $size = $request->request->get('size', 'M'); // Taille par défaut si non sélectionnée

        // Vérifie si le produit avec cette taille est déjà dans le panier
        $found = false;
        foreach ($cart as &$item) {
            if ($item['id'] === $id && $item['size'] === $size) {
                $item['quantity'] += 1;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $cart[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'price' => $product->getPrice(),
                'size' => $size,
                'quantity' => 1,
                'image' => $product->getImage() ?? 'placeholder.png' // 🔹 Ajout de l'image par défaut si elle n'existe pas
            ];
        }

        $session->set('cart', $cart);

        $this->addFlash('success', 'Produit ajouté au panier !');

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/remove/{id}/{size}', name: 'app_cart_remove', methods: ['GET'])]
    public function removeFromCart(SessionInterface $session, int $id, string $size): Response
    {
        $cart = $session->get('cart', []);

        foreach ($cart as $key => $item) {
            if ($item['id'] === $id && $item['size'] === $size) {
                unset($cart[$key]);
                break;
            }
        }

        $session->set('cart', array_values($cart)); // Réindexe le tableau

        $this->addFlash('info', 'Produit retiré du panier.');
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/clear', name: 'app_cart_clear', methods: ['GET'])]
    public function clearCart(SessionInterface $session): Response
    {
        $session->remove('cart');

        $this->addFlash('warning', 'Le panier a été vidé.');
        return $this->redirectToRoute('app_cart');
    }
}
