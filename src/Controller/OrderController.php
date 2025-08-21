<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\Order;
use App\Services\Cart;
use App\Form\OrderType;
use App\Entity\OrderProducts;
use Symfony\Component\Mime\Email;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Services\StripePayment;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderController extends AbstractController
{

    public function __construct(private MailerInterface $mailer){
    
    }

    #region ORDER
    #[Route('/order', name: 'app_order')]
    public function index(EntityManagerInterface $entityManager, ProductRepository $productRepository, 
                            SessionInterface $session, Request $request, Cart $cart): Response
    {
        $data = $cart->getCart($session);   // récupére les données du panier

        $order = new Order();
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

                if (!empty($data['total'])){
                    $totalPrice = $data['total'] + $order->getCity()->getShippingCost();
                    $order->setTotalPrice($totalPrice);
                    $order->setCreatedAt(new \DateTimeImmutable());
                    $order->setIsPaymentCompleted(0);
                    $entityManager->persist($order);
                    $entityManager->flush();
                    // dd($data['cart']);

                foreach($data['cart'] as $value) { // pour chaque élément ds le panier
                    $orderProduct = new OrderProducts();
                    $orderProduct ->setOrder($order);
                    $orderProduct->setProduct($value['product']);
                    $orderProduct->setQuantity($value['quantity']);
                    $entityManager->persist($orderProduct);
                    $entityManager->flush();
                }

                if($order->isPayOnDelivery()){
                     // Mise à jour du contenu du panier en session, après avoir flush
                    $session->set('cart', []);
    
                     //créér une vue mail
                    $html = $this->renderView('mail/orderConfirm.html.twig', [
                        'order'=>$order // On récupère le order après le flush pour avoir ttes les infos
                    ]);
                    $email = (new Email())  // on importe la classe depuis Symfony\Component\Mime\Email;
                    ->from('test@gm.com') // adresse mail à changer mettre le futur mail 
                    ->to($order->getEmail())  // adresse du receveur
                    ->subject('Confirmation de réception de commande')
                    ->html($html);
                    $this->mailer->send($email);

                    // redirection vers la page du panier
                    return $this->redirectToRoute('app_order_message');
                }
            }
            
            // Quand c'est false payment Stripe
                $paymentStripe = new StripePayment();
                $shippingCost = $order->getCity()->getShippingCost();
                $paymentStripe->startPayment($data, $shippingCost, $order->getId()); // on importe le panier et les frais de livraison
                $stripeRedirectUrl = $paymentStripe->getStripeRedirectUrl();
                
              return $this->redirect($stripeRedirectUrl);          
            }        
            return $this->render('order/index.html.twig', [
            'form' =>$form->createView(),
            'total'=>$data['total'],
        ]);
    }

#endregion ORDER
#region ORDER MESSAGE
    #[Route('/order_message', name: 'app_order_message')]
    public function orderMessage():Response
    {
        $this->addFlash('success', 'Votre commande a bien été validée !');

        return $this->render('order/orderMessage.html.twig');
    }
#endregion ORDER MESSAGE
#region CITY COST
    #[Route('/city/{id}/shipping/cost', name: 'app_city_shipping_cost')]
        public function cityShippingCost(City $city): Response
    {
        $cityShippingPrice = $city->getShippingCost();
        
        // reponse en json
        return new Response(json_encode(['status'=>200, 'message'=>'on', 'content'=> $cityShippingPrice]));

    }

#endregion CITY COST

#region EDITOR ORDERS
     #[Route('/editor/order/{type}/', name: 'app_orders_show')]
    public function getAllOrder($type, OrderRepository $orderRepository, PaginatorInterface $paginator, Request $request):Response
    {
        // wildcard pour filtrer les commandes/type
        if($type == 'is-completed'){
            $data = $orderRepository->findBy(['isCompleted'=>1], ['id'=>'DESC']);
        }else if($type == 'pay-on-stripe-not-delivered'){
            $data = $orderRepository->findBy(['isCompleted'=>null, 'pay-on-delivery'=>0,'is_payment_completed'=>1]);
        }

        $data = $orderRepository->findAll();
        $orders = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1), // met en place la pagination
            8 // je choisis 8 articles par page
        );

        return $this->render('order/orders.html.twig', [
            // 'controller_name' => 'OrderController',
            'orders' => $orders,
        ]);
    } 
#endregion EDITOR ORDERS
#region UPDATE

     #[Route('/editor/order/{id}/is-completed/update', name: 'app_orders_is-completed-update')]
     public function isCompletedUpdate (Request $request, $id, OrderRepository $orderRepository, EntityManagerInterface $entityManager):Response
     {
        
        $order = $orderRepository->find($id);
        $order->setIsCompleted(true);
        $entityManager->flush();
        $this->addFlash('success', 'La modification a bien été effectuée !');
        return $this->redirect($request->headers->get('referer'));
     }
#endregion UPDATE
#region DELETE
     #[Route('/editor/order/{id}/remove', name: 'app_orders_remove')]
     public function removeOrder (Order $order, EntityManagerInterface $entityManager):Response
     {
        
        $entityManager->remove($order);
        $entityManager->flush();
        $this->addFlash('danger', 'La suppression a bien été effectuée !');
        return $this->redirectToRoute('app_orders_show');
     }
#endregion DELETE

}
