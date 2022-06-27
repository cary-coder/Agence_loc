<?php

namespace App\Controller;

use DateTime;
use App\Entity\Vehicule;
use App\Form\VehiculeType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class VehiculeController extends AbstractController
{
    #[Route('/vehicules', name: 'app_vehicules')]
    public function allVehicules(ManagerRegistry $doctrine): Response
    {
        $vehicules = $doctrine->getRepository(Vehicule::class)->findAll();
       //dd($vehicules);
        return $this->render('vehicule/allVehicules.html.twig',[
            'vehicules'=>$vehicules,
        ]);
    }

/**
 * @Route("ajout-vehicule", name="vehicule_ajout")
 */
 public function ajout(ManagerRegistry $doctrine, Request $request )
 {
//on crée un objet vehicule
$vehicule = new Vehicule();
// on crée le form en liant le FormType à l'objet crée
    $form=$this->createForm(VehiculeType::class, $vehicule);
    //on donne accés aux données du form pour validation des données
    $form->handleRequest($request);
    // si le formulaire est soumis et validé
    if ($form->isSubmitted() && $form->isValid())
    {
        // je m'occupe d'affecter les données manquantes (qui ne parviennent pas
        //du formulaire)
        $vehicule->setDateEnregistrement(new DateTime("now"));
        //on récupère le manager de doctrine
        $manager = $doctrine->getManager();
        // on persist l'objet
        $manager->persist($vehicule);
        // puis envoie en bdd
        $manager->flush();

        return $this->redirectToRoute("app_vehicules");
    } 
        return $this->render("vehicule/formulaire.html.twig",[
            'formVehicule' => $form->createView()
        ]);
 }

     /**
     * @Route ("/update-vehicule/{id<\d+>}", name="vehicule_update")
    */
    public function updateVehicule(ManagerRegistry $doctrine, $id, Request $request)
    {
            //on recupére le vehicule dont l'id est celui passé en paramètre de la fonction 
            $vehicule = $doctrine->getRepository(Vehicule::class)->find($id);
            //dd($vehicule);
            // on crée le form en liant le FormType à l'objet crée
            $form=$this->createForm(VehiculeType::class, $vehicule);
            //on donne accés aux données du form pour validation des données
            $form->handleRequest($request);
            // si le formulaire est soumis et validé
            if ($form->isSubmitted() && $form->isValid())
            {
                //on récupère le manager de doctrine
                $manager = $doctrine->getManager();
                // on persist l'objet
                $manager->persist($vehicule);
                // puis envoie en bdd
                $manager->flush();

                return $this->redirectToRoute("app_vehicules");
            }

        return $this->render("vehicule/formulaire.html.twig", [
            'formVehicule'=> $form->createView()
        ]);
    }

    /**
    * @Route("/delete-vehicule_{id<\d+>}", name="vehicule_delete")
    */
    public function delete($id, ManagerRegistry $doctrine){
    // on recupére le vehicule à supprimer
        $vehicule = $doctrine->getRepository(Vehicule::class)->find($id);
    // on recupére le manager de doctrine
    $manager =$doctrine->getManager(); 
    // on prepare la suppression de l'vehicule
    $manager->remove($vehicule);
    //on execute la suppression dans la BDD
    $manager->flush();
    
    return $this->redirectToRoute("app_vehicules");
}

    /**
     * @Route ("/vehicule_{id<\d+>}", name="app_vehicule" )
     */

     public function show($id, ManagerRegistry $doctrine)
     {
            //on recupére le vehicule donc l'id est celui passé en paramètre de la fonction 
 
     $vehicule =$doctrine->getRepository(Vehicule::class)->find($id);
   
     return $this->render("vehicule/unVehicule.html.twig", [
        'vehicule'=>$vehicule
     ]);
     }
}

