<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\Checkout\Session;


class StripePayment {

    private $redirectUrl;

    public function __construct ()
    {
        
        Stripe::setApiKey($_SERVER['STRIPE_SECRET_KEY']);
        Stripe::setApiVersion('2025-07-30.basil');
    }

    public function startPayment($cart, $shippingCost, $orderId){
        // dd($cart);

        // Récupération des produits du panier
        $cartProducts = $cart['cart'];
        // Initialisation tableau vide pour stocker les produits formatés
        $products = [
            [
                'qte' => 1,
                'price' => $shippingCost,
                'name' => "Frais de livraison"
            ]
        ];
        // Boucle pour parcourir chaque produit du panier
        foreach ($cartProducts as $value) {
            // initialisation tableau vide pour stocker les infos d'un produit
            $productItem = [];
            //Récupération du nom du produit
            $productItem ['name'] = $value['product']->getName();
            $productItem ['price'] = $value['product']->getPrice();
            $productItem ['qte'] = $value['quantity'];
            // Ajout du produit formaté au tableau des produits
            $products[] = $productItem;
        }

        $session = Session::create([
            'line_items'=>[ // produits qui vont être payés
                array_map(fn(array $product) => [
                    'quantity' =>$product['qte'],
                    'price_data' => [
                        'currency' => 'Eur',
                        'product_data' =>[
                            'name' => $product['name']
                        ],
                        'unit_amount' => $product['price']*100, // prix donné en centimes
                    ],
                ],$products )
           
            ],
            'mode' => 'payment',
            'cancel_url' => 'http://127.0.0.1:8000/pay/cancel',
            'success_url' => 'http://127.0.0.1:8000/pay/success',
            'billing_address_collection' => 'required', // si on autorise les factures
            'shipping_address_collection' => [
                'allowed_countries' => ['FR',],
            ],
            // 'metadata' => [
            //     // 'order_id' => $cart->id
            //     'order_id' => '2'
            // ],
            'payment_intent_data' => [
                'metadata' => [
                    'orderid' =>$orderId,
                ]
            ]

        ]);

        $this->redirectUrl = $session->url;
    }

    public function getStripeRedirectUrl(){
        return $this->redirectUrl;
    }
}