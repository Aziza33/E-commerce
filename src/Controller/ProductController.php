<?php

namespace App\Controller;

use DateTimeImmutable;
use App\Entity\Product;
use App\Form\ProductType;
use App\Entity\AddProductHistory;
use App\Form\AddProductHistoryType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\AddProductHistoryRepository;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/editor/product')]
#[IsGranted("ROLE_EDITOR")]
final class ProductController extends AbstractController
{
    #[Route(name: 'app_product_index', methods: ['GET'])]
    // #[IsGranted("ROLE_ADMIN")]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }
#region ADD NEW
    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_ADMIN")]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    // interface composant string qui va transformer le lien de l'image en slug (version très simple d'une chaine de caractère), "mon image" va devenir "mon-image"
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
           
            $image = $form->get('image')->getData(); // permet de récupérer l'image et son contenu, fichier upload

            // si une image a bien été envoyée
            if($image){
                $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME); // on récupère le nom d'origine sans les extensions
                $safeImageName = $slugger->slug($originalName); // on va "slugger ou sluggifier" on remplace ts les espaces les caractères par "-"
                $newFileImageName = $safeImageName.'-'.uniqid().'.'.$image->guessExtension(); // on rajoute un id unique et donc l'extension
                
                try { // ça déplace le fichier (image) dans le dossier que j'aurai défini ds le paramètre image_directory qui se trouve ds service.yaml
                    $image->move
                    ($this->getParameter('image_directory'),
                    $newFileImageName);
                } catch (FileException $exception) {
                    // on met le message d'erreur si besoin
                }
                    $product->setImage($newFileImageName); // on sauvegarde le nom du fichier ds son entité
            }


            $entityManager->persist($product);
            $entityManager->flush();

            $stockHistory = new AddProductHistory();
            $stockHistory->setQuantity($product->getStock());
            $stockHistory->setProduct($product);
            $stockHistory->setCreatedAt(new DateTimeImmutable());

            $entityManager->persist($stockHistory);
            $entityManager->flush();

            $this->addFlash('success', 'Le produit a bien été créé !');

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }
#endRegion

#region SHOW

    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    // #[IsGranted("ROLE_ADMIN")]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }
#endREGION
#region EDIT

    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    // #[IsGranted("ROLE_ADMIN")]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager, SluggerInterface $slugger, $stock): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('image')->getData();
                if ($image){
                    $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeImageName = $slugger->slug($originalName);
                    $newFileImageName = $safeImageName.'-'.uniqid().'.'.$image->guessExtension();

                        try {
                            $image->move
                            ($this->getParameter('image_directory'),
                            $newFileImageName);
                        } catch (FileException $exception) {
                            // message le cas échéant
                        }
                        $product->setImage($newFileImageName);
                }

            // $stock = $form->get('stock')->getData();
              
            // $product->setStock($stock);
                

            $entityManager->flush();

            $this->addFlash('success', 'Le produit a bien été mis à jour !');
            
            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
            'stock' => $stock,
        ]);
    }
#endregion EDIT
#region DELETE

    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
    // #[IsGranted("ROLE_ADMIN")]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();

            $this->addFlash('danger', 'Le produit a bien été supprimé.');

        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }
#endregion
#region ADD STOCK

    #[Route('/add/product/{id}', name: 'app_product_stock_add', methods: ['GET', 'POST'])]
    // #[isGranted("ROLE_ADMIN")]
     public function stockAdd($id, ProductRepository $productRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $stockAdd = new AddProductHistory();
        $form = $this->createForm(AddProductHistoryType::class, $stockAdd);
        $form->handleRequest($request);
        $product = $productRepository->find($id);
        if ($form->isSubmitted() && $form->isValid()){

#region  AJOUT LIMITE STOCK

            if ($stockAdd->getQuantity()==0){
                $newQuantity = $product->getStock();
                $product->setStock($newQuantity);
                $entityManager->persist($stockAdd);
                $entityManager->flush();
                $this->addFlash('danger', 'Le stock est épuisé.');
            }

            if ($stockAdd->getQuantity()<=5){
                $quantity = $product->getStock();

                $this->addFlash('warning', 'Il ne reste plus que ' .$quantity .' produits en stock.');
            }
#endregion FIN AJOUT LIMITE STOCK
            
            if($stockAdd->getQuantity()>0){  // si stock > 0
                $newQuantity = $product->getStock() + $stockAdd->getQuantity(); // on additionne le stock existant au stock ajouté
                $product->setStock($newQuantity);

                $stockAdd->setCreatedAt(new DateTimeImmutable());
                $stockAdd->setProduct($product);
                $entityManager->persist($stockAdd);
                $entityManager->flush();
               
                $this->addFlash('success', 'Le stock du produit a bien été mis à jour !');                        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
                return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
            }else{
                $this->addFlash('danger', 'Le stock du produit ne doit pas être inférieur à zéro.');
                return $this->redirectToRoute('app_product_stock_add', ['id'=>$product->getId()]);
            }
        
            }
              
            return $this->render('product/addStock.html.twig',
                ['form'=>$form->createView(),
                'product' => $product,
                ]
            );
        // return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }

#region Show HISTORY
    #[Route('/add/product/{id}/stock/history', name: 'app_product_stock_add_history', methods: ['GET'])]
    public function showHistoryProductStock($id, ProductRepository $productRepository, AddProductHistoryRepository $addProductHistoryRepository): Response
    {
        $product = $productRepository->find($id); // on récupère le produit passé en paramètre
        $productAddHistory = $addProductHistoryRepository->findBy(['product'=>$product], ['id'=>'DESC']);

    return $this->render('product/addHistoryStockShow.html.twig',
        [ 'productsAdded' => $productAddHistory,
          'product' => $product
        ]);
    }

#endRegion
// #region AFFICHER TOUT
// #[Route('/', name: 'app_product_list', methods: ['GET'])]
// public function list(ProductRepository $productRepository): Response
// {
//     // On récupère tous les produits
//     $products = $productRepository->findAll();

//     // On les envoie à la vue
//     return $this->render('product/list.html.twig', [
//         'products' => $products,
//     ]);
// }

}
