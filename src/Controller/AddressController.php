<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/account/address')]
class AddressController extends AbstractController
{
    #[Route('/edit', name: 'app_edit_address')]
    #[IsGranted('ROLE_USER')]
    public function editAddress(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
    
        if (!$user) {
            throw $this->createAccessDeniedException("Vous devez être connecté.");
        }
    
        if ($request->isMethod('POST')) {
            $user->setFullName($request->request->get('fullName'));
            $user->setStreet($request->request->get('street'));
            $user->setPostalCode($request->request->get('postalCode'));
            $user->setCity($request->request->get('city'));
            $user->setPhoneNumber($request->request->get('phoneNumber'));
    
            $entityManager->persist($user);
            $entityManager->flush();
    
            $this->addFlash('success', 'Adresse mise à jour avec succès.');
            return $this->redirectToRoute('app_account');
        }
    
        return $this->render('account/edit_address.html.twig', [
            'user' => $user,
        ]);
    }

}