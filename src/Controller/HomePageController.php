<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\SubCategoryRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class HomePageController extends AbstractController
{
    #[Route('/', name: 'app_home_page', methods: ('GET'))]
    public function index($subCategories, ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {
        return $this->render('home_page/index.html.twig', [
            'controller_name' => 'HomePageController',
            'products'=>$productRepository->findAll(),
            'categories'=>$categoryRepository->findAll(),
            'subCategories'=>$subCategories
            
        ]);
    }


     #[Route('/', name: 'app_home_page', methods: ['GET'])]
    public function afficherTout(ProductRepository $productRepository): Response
    {
        // Récupérer tous les produits
        // $products = $productRepository->findAll();

        // Envoyer à la vue
        return $this->render('home_page/index.html.twig', [
            'products'=>$productRepository->findAll(),
            // 'products' => $products,
        ]);
    }

      #[Route('/product/{id}/show', name: 'app_home_product_show', methods: ['GET'])]
    public function showProduct(Product $product, ProductRepository $productRepository): Response
    {
        // le 1er arg est un tableau vide, càd je récupère sans filtrage, tu tries par id décroissant du plus ancien au plus récent, limite à 5 
       $lastProductsAdd = $productRepository->findBy([],['id'=>'DESC'], 5);

        // Envoyer à la vue
        return $this->render('home_page/show.html.twig', [
            'product' => $product,
            'products' => $lastProductsAdd
        ]);
    }
     #[Route('/product/subcategory/{id}/filter', name: 'app_home_product_filter', methods: ['GET'])]
    public function filter($id, SubCategoryRepository $subCategoryRepository, CategoryRepository $categoryRepository): Response
    {
            // on récupère la ss catég correspondante à l'id passé en paramètre
            // on accède aux products de cette ss catégorie
            $product = $subCategoryRepository->find($id)->getProducts();
            // on récupère la ss cat complète (objet)
            $subCategory = $subCategoryRepository->find($id);

            return $this->render('home_page/filter.html.twig', [
                'products' => $product, // liste des produits liés à la ss cat
                'subCategory' => $subCategory, // l'objet ss cat qui corrrespond à l'id
                'categories' => $categoryRepository->findAll() // la liste de ttes les catégories via le repo
            ]);
    }

}
