<?php

// src/Controller/VillaController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VillaController extends AbstractController
{
    #[Route('/villa', name: 'villa_58')]
    public function villa58(): Response
    {
        return $this->render('villa/villa58.html.twig');
    }
}
