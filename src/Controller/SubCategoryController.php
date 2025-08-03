<?php

namespace App\Controller;

use App\Entity\SubCategory;
use App\Form\SubCategoryType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\SubCategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route('/sub/category')]
#[IsGranted("ROLE_ADMIN")]
final class SubCategoryController extends AbstractController
{
    #[Route(name: 'app_sub_category_index', methods: ['GET'])]
    #[IsGranted("ROLE_ADMIN")]
    public function index(SubCategoryRepository $subCategoryRepository): Response
    {
        return $this->render('sub_category/index.html.twig', [
            'sub_categories' => $subCategoryRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_sub_category_new', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_ADMIN")]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $subCategory = new SubCategory();
        $form = $this->createForm(SubCategoryType::class, $subCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($subCategory);
            $entityManager->flush();

            $this->addFlash('success', 'The new subcategory have been added !');

            return $this->redirectToRoute('app_sub_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sub_category/new.html.twig', [
            'sub_category' => $subCategory,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_sub_category_show', methods: ['GET'])]
    #[IsGranted("ROLE_ADMIN")]
    public function show(SubCategory $subCategory): Response
    {
        return $this->render('sub_category/show.html.twig', [
            'sub_category' => $subCategory,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_sub_category_edit', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_ADMIN")]
    public function edit(Request $request, SubCategory $subCategory, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SubCategoryType::class, $subCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'The subcategory have been updated !');

            return $this->redirectToRoute('app_sub_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sub_category/edit.html.twig', [
            'sub_category' => $subCategory,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_sub_category_delete', methods: ['POST'])]
    #[IsGranted("ROLE_ADMIN")]
    public function delete(Request $request, SubCategory $subCategory, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$subCategory->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($subCategory);
            $entityManager->flush();

            $this->addFlash('danger', 'The subcategory have been deleted !');
        }

        return $this->redirectToRoute('app_sub_category_index', [], Response::HTTP_SEE_OTHER);
    }
}