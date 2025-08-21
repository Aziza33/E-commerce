<?php

namespace App\Controller;

use Stripe\Stripe;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class StripeController extends AbstractController
{
    #[Route('/pay/success', name: 'app_stripe_success')]
    public function success(): Response
    {
        $this->addFlash('success', 'Votre paiement a bien été effectué');
        return $this->render('stripe/index.html.twig', [
            'controller_name' => 'StripeController',
        ]);
    }

     #[Route('/pay/cancel', name: 'app_stripe_cancel')]
    public function cancel(): Response
    {
        $this->addFlash('danger', 'Votre paiement n\'a pas pu aboutir');
        return $this->render('stripe/index.html.twig', [
            'controller_name' => 'StripeController',
        ]);
    }
     #[Route('/stripe/notify', name: 'app_stripe_notify')]
    public function stripeNotify(Request $request, OrderRepository $orderRepository, EntityManagerInterface $entityManager): Response
    {
        // définir clé secrète
        Stripe::setApiKey($_SERVER['STRIPE_SECRET_KEY']);
        // définir la clé de webhook de Stripe
        $endpoint_secret = $_SERVER['STRIPE_WEBHOOK_SECRET'];
        // récupérer le contenu de la requête
        $payload = $request->getContent();
        // file_put_contents("log.txt", $payload, FILE_APPEND);
        //récupérer l'en-tête et signature de la requête
        $sigHeader = $request->headers->get('Stripe-Signature');
        // initialiser l'évènement à nul
        $event = null;

        try {
            // construire l'évènement à partir de la requête et de la signature
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sigHeader, $endpoint_secret
            );
            //file_put_contents("log.txt", "try ok", FILE_APPEND);
        } catch (\UnexpectedValueException $e) {
            // retourner une erreur 400 si le payload est invalide
            return new Response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // retourner une erreur 400 si la signature est invalide
            return new Response('Invalid Signature', 400);
        }
        // file_put_contents("log.txt", $event->type, FILE_APPEND);

        // Gérer les différents types d'événements
        switch ($event->type) {
            case 'payment_intent.succeeded': 
                // file_put_contents("log.txt", "succeeded");
                // récupérer l'obj payment_intent
                $paymentIntent = $event->data->object;

                // Enregistrer les détails du paiement dans un fichier
                // $fileName = 'stripe-detail-'.uniqid().'.txt';
                // file_put_contents($fileName, $paymentIntent);

                $orderId = $paymentIntent->metadata->orderid;
                $order = $orderRepository->find($orderId);
                $cartPrice = $order->getTotalPrice();
                $stripeTotalAmount = $paymentIntent->amount/100;
                if($cartPrice == $stripeTotalAmount){
                    $order->setIsPaymentCompleted(1);
                    $entityManager->flush();
                }

                break;
            case 'payment_method.attached':
                $paymentMethod = $event->data->object;
                break;
            default :
                // ne rien faire pour les autres événements
                break;
        }

        return new Response('Evénement reçu avec succès', 200);

    }
}
