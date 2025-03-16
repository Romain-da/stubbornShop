<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/products')]
#[IsGranted('ROLE_ADMIN')]
final class AdminProductController extends AbstractController
{
    #[Route('/', name: 'admin_product_index')]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('admin/product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/manage/{id?}', name: 'admin_product_manage', methods: ['GET', 'POST'])]
    public function manage(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, ?Product $product = null): Response
    {
        // Si l'ID est défini, on récupère le produit existant, sinon on crée un nouveau produit
        if (!$product) {
            $product = new Product();
        }

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $imageFile */
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $newFilename = $this->handleImageUpload($imageFile, $slugger);
                if ($newFilename) {
                    // Supprimer l'ancienne image si elle existe
                    if ($product->getImage()) {
                        $this->deleteImageFile($product->getImage());
                    }
                    $product->setImage($newFilename);
                }
            }

            // ✅ Gestion du champ "Mettre en avant"
            $isFeatured = $request->request->get('is_featured') ? true : false;
            $product->setIsFeatured($isFeatured);

            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', 'Le produit a été sauvegardé avec succès !');
            return $this->redirectToRoute('admin_product_index');
        }

        return $this->render('admin/product/manage.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('_token'))) {
            if ($product->getImage()) {
                $this->deleteImageFile($product->getImage());
            }
            $entityManager->remove($product);
            $entityManager->flush();

            $this->addFlash('success', 'Le produit a été supprimé avec succès.');
        }

        return $this->redirectToRoute('admin_product_index');
    }

    /**
     * 🔹 Gère l'upload d'image et retourne le nom du fichier.
     */
    private function handleImageUpload(UploadedFile $imageFile, SluggerInterface $slugger): ?string
    {
        $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

        try {
            $imageFile->move(
                $this->getParameter('product_images_directory'),
                $newFilename
            );
            return $newFilename;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 🔹 Supprime une image du répertoire.
     */
    private function deleteImageFile(string $filename): void
    {
        $filePath = $this->getParameter('product_images_directory') . '/' . $filename;
        if (file_exists($filePath) && is_file($filePath)) {
            unlink($filePath);
        }
    }
}
