<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/product')]
final class ProductController extends AbstractController
{
    #[Route(name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository, Request $request): Response
    {
        $minPrice = $request->query->get('minPrice');
        $maxPrice = $request->query->get('maxPrice');

        $query = $productRepository->createQueryBuilder('p');

        if ($minPrice && $maxPrice) {
            $query->where('p.price BETWEEN :minPrice AND :maxPrice')
                  ->setParameter('minPrice', $minPrice)
                  ->setParameter('maxPrice', $maxPrice);
        }

        return $this->render('product/index.html.twig', [
            'products' => $query->getQuery()->getResult(),
        ]);
    }

    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $product = new Product();
        $product->setCreatedAt(new \DateTimeImmutable()); // 🔹 Ajoute la date de création

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload d'image
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $newFilename = $this->handleImageUpload($imageFile, $slugger);
                if ($newFilename) {
                    $product->setImage($newFilename);
                }
            }

            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload d'image
            /** @var UploadedFile $imageFile */
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

            $entityManager->flush();
            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('_token'))) {
            // Supprimer l'image associée
            if ($product->getImage()) {
                $this->deleteImageFile($product->getImage());
            }
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Gère l'upload d'image et retourne le nom de fichier.
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
        } catch (FileException $e) {
            return null;
        }
    }

    /**
     * Supprime une image du répertoire.
     */
    private function deleteImageFile(string $filename): void
    {
        $filePath = $this->getParameter('product_images_directory') . '/' . $filename;
        if (file_exists($filePath)) {
            try {
                unlink($filePath);
            } catch (\Exception $e) {
                // Log error if necessary
            }
        }
    }
}
