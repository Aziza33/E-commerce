<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SearchEngineController extends AbstractController
{
    #[Route('/search/engine', name: 'app_search_engine')]
    public function index(Request $request, ProductRepository $productRepository): Response
    {
        if($request->isMethod('POST') ){
            // récupère les données de la requête
            $word = $request->get('word');
            // Récupère le mot clé de recherche
            // $word = $data['word'];
            // Appelle la méthode searchEngine du repository
            $results = $productRepository->searchEngine($word);
        }
        return $this->render('search_engine/index.html.twig', [
            // 'controller_name' => 'SearchEngineController',
            'products' => $results,
            'word' => $word,
        ]);
    }
}
