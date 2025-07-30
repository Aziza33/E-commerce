<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryFormType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

final class CategoryController extends AbstractController
{
    #[Route('/admin/category', name: 'app_category')]
    public function index(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();
        return $this->render('category/index.html.twig', [
            'controller_name' => 'CategoryController',
            'categories' => $categories,
            
        ]);
    }
// ...................   AJOUTER   .........................................

    #[Route('/admin/category/new', name: 'app_category_new')]
    public function addCategory(EntityManagerInterface $entityManager, Request $request): Response

    // Méthode du contrôle qui gère la création d'une nouvelle catégorie
    // Elle a pour paramètre le gestionnaire d'entité pour la bdd et la requête HTTP, elle renvoie une réponse
    {
        $category = new Category();
        //Création d'une nouvelle instance de l'entity Catégorie

        $form = $this->createForm(CategoryFormType::class, $category);
        // Création d'un formulaire basé sur la classe CategoryFormType lié à l'objet category
        $form -> handleRequest($request);
        // Traite les données envoyées dans la requête pour remplir le formulaire
        
        if ($form->isSubmitted() && $form->isValid()){
            // prépare l'objet category a être envoyé en bdd
            $entityManager->persist($category);
            // Execute la requête d'insertion en bdd et sauvegarde ces infos en bdd
            $entityManager->flush();   
            $this->addFlash('success', 'La catégorie a bien été ajoutée.');
            // redirige vers la route précisée ds le code
              return $this->redirectToRoute('app_category'); 
        }
        return $this->render('category/newCategory.html.twig', [
            // affiche le formulaire s'il est soumis et valide
            'form' => $form->createView(),             
        ]);
    }

    //  ................  UPDATE  ..........................................
      #[Route('/admin/category/update/{id}', name: 'app_category_update')]

    public function updateCategory($id, Request $request, EntityManagerInterface $entityManager, CategoryRepository $categoryRepository): Response
    {
        
        // on récupère l'id
        $crud = $entityManager->getRepository(Category::class)->find($id);
        // on créé un formulaire
        $form = $this->createForm(CategoryFormType::class, $crud);
        // on fait la requête
        $form->handleRequest($request);  
        // on check le formulaire
         if ($form->isSubmitted() && $form->isValid()){
            $entityManager->persist($crud); // on persiste
            $entityManager->flush();   // on execute la mise à jour
            $this->addFlash('success', 'La catégorie a bien été modifiée.'); 
            return $this->redirectToRoute('app_category'); 
    }
    return $this->render('category/updateCategory.html.twig', [
           'form' => $form->createView()
        ]);
    }

    //  ................  DELETE  ..........................................


    #[Route('/admin/category/delete/{id}', name: 'app_category_delete')]
    public function deleteCategory(Category $category, EntityManagerInterface $entityManager): Response
    {
        // $crud = $entityManager->getRepository(Category::class)->find($id);       
        $entityManager->remove($category);
        $entityManager->flush();   
        // $this->addFlash('notice', 'Suppression effectuée');
        $this->addFlash('danger', 'La catégorie a bien été supprimée.');
        
            return $this->redirectToRoute('app_category'); 
        }
}
