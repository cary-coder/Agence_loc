<?php

namespace App\Controller;

use App\Entity\Vehicule;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ClientController extends AbstractController
{
    #[Route('client/vehicules', name: 'client_app_client')]
    public function allVehicules(ManagerRegistry $doctrine): Response
    {
                //code pour vérifier l'autentification, si l'user n'est pas connecté
     if( !$this->isGranted('IS_AUTHENTICATED_FULLY')){
         $this->addFlash('error', "Veuillez vous connecter pour acceder à cette page!");
         return $this->redirectToRoute('app_login');
        }
    //     // si l'utilisateur est connecté mais n'est pas admin
    // if(!$this->isGranted("ROLE_ADMIN")){
    //     $this->addFlash('error', "Vous êtes un intrus, vous ne pouvez pas vous connecter à cette page!!!");
    //     return $this->redirectToRoute('app_home');
    // }
        $vehicules = $doctrine->getRepository(Vehicule::class)->findAll();
        return $this->render('client/clientVehicules.html.twig', [
            'vehicules' => $vehicules,
        ]);
    }

}                                             
