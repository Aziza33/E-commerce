<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class CartController extends AbstractController
{
    // service (objet qui fournit une fonctionnalité spécifique)
    public function __construct(private readonly ProductRepository $productRepository) 
    {
        // on définit ce qui sera accesssible que depuis cette classe, c'est pour l'encapsulation (niveau de sécurité), on prépare l'injection de dépendance d'un certain service plus tard ds le cours
        // readonly consommable que depuis l'intérieur de la classe, propriété assignée qu'une seule fois ds le constructeur, on aura accès au getter ms pas au setter, donc on ne pt pas modifier le panier
    }
#region CREATION DU PANIER
    #[Route('/cart', name: 'app_cart', methods: ['GET'])]
    public function index(SessionInterface $session): Response
    {   
        // récupére les données du panier
        $cart = $session->get('cart', []);
        // initialise un tableau pour stocker les données du panier avec les informations de produits
        $cartWithData = [];

        // Boucle sur les éléments du panier pour récupérer les informations de produit
        foreach ($cart as $id => $quantity) {
            // récupère le produit correspondant à l'id et à la quantité
            $cartWithData[] = [
                'product' => $this->productRepository->find($id),
                'quantity'=> $quantity
            ];
        }
        // calcul total du panier, on mappe sur le tableau pour récupérer ts les items
        $total = array_sum(array_map(function ($item){
            // Pour chaque élément du panier, multiplie le prix du produit par la quantité
             return $item['product']->getPrice() * $item['quantity'];
        }, $cartWithData));
            
        // retourne la vue pour afficher le panier
        return $this->render('cart/index.html.twig', [
            'controller_name' => 'CartController',
            'item' => $cartWithData, // on retourne ces deux variables afin de la récupérer ds la vue
            'total' => $total
        ]);
    }
#endregion CREATION
#region ADD PRODUCTS
        #[Route('/cart/add/{id}', name: 'app_cart_add', methods: ['GET'])]
        // Définit une route pour ajouter un produit au panier
        public function addProductToCart(int $id, SessionInterface $session): Response // int veut dire type integer obligatoire, + sécurisé
        // Méthode pour ajouter un produit au panier, prend l'ID du produit et la session en paramètres
        {
            $cart = $session->get('cart', []);
            // récupère le panier actuel de la session, ou un tableau vide s'il n'existe pas
            if (!empty($cart[$id])){
                $cart[$id]++;
            }else{
                $cart[$id]=1;
            }
            // Si le produit est déjà ds le panier, incrémente sa quantité sinon l'ajoute avec une quantité de 1
            $session->set('cart', $cart);
            // Met à jour le panier ds la session et redirige vers la page du panier
            return $this->redirectToRoute('app_cart');
        }
#endregion ADD PRODUCTS


}
