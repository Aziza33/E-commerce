<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\Order;
use App\Services\Cart;
use App\Form\OrderType;
use App\Entity\OrderProducts;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class OrderController extends AbstractController
{
    #[Route('/order', name: 'app_order')]
    public function index(EntityManagerInterface $entityManager, ProductRepository $productRepository, 
                            SessionInterface $session, Request $request, Cart $cart): Response
    {
        $data = $cart->getCart($session);   // récupére les données du panier

        $order = new Order();
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            if($order->isPayOnDelivery()){
                // dd($order);

                if (!empty($data['total'])){
                    $order->setTotalPrice($data['total']);
                    $order->setCreatedAt(new \DateTimeImmutable());
                    $entityManager->persist($order);
                    $entityManager->flush();
                    // dd($data['cart']);

                foreach($data['cart'] as $value) {
                    $orderProduct = new OrderProducts();
                    $orderProduct ->setOrder($order);
                    $orderProduct->setProduct($value['product']);
                    $orderProduct->setQuantity($value['quantity']);
                    $entityManager->persist($orderProduct);
                    $entityManager->flush();
                }
            }

            // Mise à jour du contenu du panier en session
            $session->set('cart', []);
            // Redirection vers la page du panier
            return $this->redirectToRoute('order_message');
                
            }
        }

        return $this->render('order/index.html.twig', [
            'form' =>$form->createView(),
            'total'=>$data['total'],
        ]);
    }

    #[Route('/city/{id}/shipping/cost', name: 'app_city_shipping_cost')]
        public function cityShippingCost(City $city): Response
    {
        $cityShippingPrice = $city->getShippingCost();
        
        // reponse en json
        return new Response(json_encode(['status'=>200, "message"=>'on', 'content'=> $cityShippingPrice]));

        // dd($city);
    }

    #[Route('/order_message', name: 'order_message')]
    public function orderMessage():Response
    {
        $this->addFlash('success', 'Votre commande a bien été validée !');

        return $this->render('order/orderMessage.html.twig');
    }

     #[Route('/editor/orders', name: 'app_orders_show')]
    public function getAllOrder(OrderRepository $orderRepository, PaginatorInterface $paginator, Request $request):Response
    {

        $data = $orderRepository->findAll();
        $orders = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1), // met en place la pagination
            8 // je choisis 8 articles par page
        );
        // $city = $city->getName();
        // $data = $data['total'];

        return $this->render('order/orders.html.twig', [
            'controller_name' => 'OrderController',
            'orders' => $orders,
            // 'city' => $city
            
            // 'total'=>$data['total']
        ]);
    } 

}
