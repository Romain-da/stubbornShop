<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
        ]);
    }

    #[Route('/add/{id}', name: 'app_cart_add', methods: ['POST'])]
    public function addToCart(Request $request, ProductRepository $productRepository, SessionInterface $session, int $id): Response
    {
        $product = $productRepository->find($id);
        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé.');
        }

        $size = $request->request->get('size');
        if (!$size) {
            $this->addFlash('danger', 'Veuillez choisir une taille.');
            return $this->redirectToRoute('app_product_show', ['id' => $id]);
        }

        // Récupération du panier existant
        $cart = $session->get('cart', []);

        // Vérifie si l'article existe déjà dans le panier avec la même taille
        $found = false;
        foreach ($cart as &$item) {
            if ($item['id'] === $id && $item['size'] === $size) {
                $item['quantity']++; // Augmente la quantité
                $found = true;
                break;
            }
        }

        // Si le produit n'est pas encore dans le panier, l'ajouter
        if (!$found) {
            $cart[] = [
                'id' => $id,
                'name' => $product->getName(),
                'price' => $product->getPrice(),
                'size' => $size, 
                'quantity' => 1,
            ];
        }

        $session->set('cart', $cart);

        $this->addFlash('success', 'Produit ajouté au panier avec succès !');
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
