<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;


final class UserController extends AbstractController
{
    #[Route('/admin/user', name: 'app_user')]
    public function index(UserRepository $userRepository): Response
    {   
        
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
            //méthode plus rapide et concise pour récupérer ts les users en une seule ligne
            //on crée la variable frontend users qui aura pour valeur toutes les valeurs qu'on consomme immédiatement
            'users' => $userRepository->findAll(),         
        ]);

    }

    // ..................    Update Role    ..............................

    #[Route('/admin/user/{id}', name: 'app_user_update_role')]
    public function updateRole(User $user, EntityManagerInterface $entityManager): Response
    {   
        // changer le rôle
        $user->setRoles(['ROLE_EDITOR', 'ROLE_USER']);

        // persist et flush
        // $entityManager->persist($user); 
        $entityManager->flush();   // on execute la mise à jour
        // message flash
        $this->addFlash('success', 'Le rôle a bien été ajouté.'); 

        // redirection page users/index
        return $this->redirectToRoute('app_user');
    }


    // .......................   Supprimer le rôle editor  ........................................

     #[Route('/user/role/update/{id}', name: 'app_user_delete_role')]
     #[IsGranted("ROLE_ADMIN")]
    public function removeRoleEditor(User $user, EntityManagerInterface $entityManager): Response
    {   
        // $roles = $user->getRoles();
        // changer le rôle et flush
        $user->setRoles([]); 
        // $newRoles = array_diff($roles, ['ROLE_EDITOR']);
        // $user->setRoles($newRoles);
        $entityManager->flush();   // on execute la mise à jour
        // message flash
        $this->addFlash('success', 'Le rôle editor a bien été supprimé.'); 

        // redirection page users
        return $this->redirectToRoute('app_user');
    }

    // ....................  Delete l'utilisateur .......................;

    #[Route('/user/delete/{id}', name: 'app_user_delete')]
    #[isGranted("ROLE_ADMIN")]
    public function deleteUser(EntityManagerInterface $entityManager, $id): Response
    {
        // on récupère l'id
        $crud = $entityManager->getRepository(User::class)->find($id);
        //on supprime le user
        $entityManager->remove($crud);
        $entityManager->flush();
        // message flash
        $this->addFlash('success', 'L\'utilisateur a bien été supprimé.');
        // Retourne la route
        return $this->redirectToRoute('app_user');
    }
}
