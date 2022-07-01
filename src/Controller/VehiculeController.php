<?php

namespace App\Controller;

use DateTime;
use App\Entity\Vehicule;
use App\Form\VehiculeType;
use App\Repository\VehiculeRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class VehiculeController extends AbstractController

//***************ROUTE ADMIN/ GESTION ALL VEHICULES ********************/
{
    #[Route('admin/vehicules', name: 'admin_app_vehicules')]
    public function allVehicules(ManagerRegistry $doctrine): Response
    {
        //code pour vérifier l'autentification, si l'user n'est pas connecté
    if( !$this->isGranted('IS_AUTHENTICATED_FULLY')){
         $this->addFlash('error', "Veuillez vous connecter pour acceder à cette page!");
         return $this->redirectToRoute('app_login');
        }
        // si l'utilisateur est connecté mais n'est pas admin
    if(!$this->isGranted("ROLE_ADMIN")){
        $this->addFlash('error', "Vous êtes un intrus, vous ne pouvez pas vous connecter à cette page!!!");
        return $this->redirectToRoute('app_home');
    }
        $vehicules = $doctrine->getRepository(Vehicule::class)->findAll();
       //dd($vehicules);
        return $this->render('vehicule/admin/allVehicules.html.twig',[
            'vehicules'=>$vehicules
        ]);
    }

//***************ROUTE ADMIN AJOUT VEHICULES ********************/

/**
 * @Route("admin/ajout-vehicule", name="admin_vehicule_ajout")
 */
 public function ajout(ManagerRegistry $doctrine, Request $request, SluggerInterface $slugger )
 {
       //code pour vérifier l'autentification, si l'user n'est pas connecté
     if( !$this->isGranted('IS_AUTHENTICATED_FULLY')){
         $this->addFlash('error', "Veuillez vous connecter pour acceder à cette page!");
         return $this->redirectToRoute('app_login');
        }
        // si l'utilisateur est connecté mais n'est pas admin
    if(!$this->isGranted("ROLE_ADMIN")){
        $this->addFlash('error', "Vous êtes un intrus, vous ne pouvez pas vous connecter à cette page!!!");
        return $this->redirectToRoute('app_home');
    }
        //on crée un objet vehicule
        $vehicule = new Vehicule();
        // on crée le form en liant le FormType à l'objet crée
            $form=$this->createForm(VehiculeType::class, $vehicule);
            //on donne accés aux données du form pour validation des données
            $form->handleRequest($request);
            // si le formulaire est soumis et validé
            if ($form->isSubmitted() && $form->isValid())
    {
        //on recupére l'image depuis le formulaire car elle n'est pas Null
        $file =$form-> get('imageForm')->getData();
        //dd($file);
        //dd($vehicule);
        //le slug permet de modifier une chaine de caractéres : mot clé => mot-clé 
        $fileName =$slugger -> slug($vehicule->getTitre()) . uniqid() . '.' .$file->guessExtension();
        try {
            //on deplace le fichier image recuperé despuis le formulaire dans le
            //dossier parametré dans la partie Parameters du fichier config/service.yaml, avec pour nom $fileName
            $file->move($this->getParameter('photos_vehicules'), $fileName);
        } catch (FileException ) {
            //gerer les exceptions en cas d'erreur durant l'upload
        } 
        $vehicule->setPhoto($fileName);
        // je m'occupe d'affecter les données manquantes (qui ne parviennent pas
        //du formulaire)
        $vehicule->setDateEnregistrement(new DateTime("now"));
        //on récupère le manager de doctrine
        $manager = $doctrine->getManager();
        // on persist l'objet
        $manager->persist($vehicule);
        // puis envoie en bdd
        $manager->flush();
        $this->addFlash('success', 'Le vehicule a bien été ajouté!');

        return $this->redirectToRoute("admin_app_vehicules");
    } 
        return $this->render("vehicule/admin/formulaire.html.twig",[
            'formVehicule' => $form->createView()
        ]);
 }

//***************ROUTE ADMIN UPDATE VEHICULES ********************/
/**
 * @Route ("/admin/update-vehicule_{id<\d+>}", name="admin_vehicule_update")
*/
    public function updateVehicule(ManagerRegistry $doctrine, $id, Request $request,  SluggerInterface $slugger)
    {
        //code pour vérifier l'autentification, si l'user n'est pas connecté
     if( !$this->isGranted('IS_AUTHENTICATED_FULLY')){
         $this->addFlash('error', "Veuillez vous connecter pour acceder à cette page!");
         return $this->redirectToRoute('app_login');
        }
        // si l'utilisateur est connecté mais n'est pas admin
    if(!$this->isGranted("ROLE_ADMIN")){
        $this->addFlash('error', "Vous êtes un intrus, vous ne pouvez pas vous connecter à cette page!!!");
        return $this->redirectToRoute('app_home');
    }
            //on recupére le vehicule dont l'id est celui passé en paramètre de la fonction 
            $vehicule = $doctrine->getRepository(Vehicule::class)->find($id);
            //dd($vehicule);
            //on crée le form en liant le FormType à l'objet crée
            $form=$this->createForm(VehiculeType::class, $vehicule);
            //on donne accés aux données du form pour validation des données
            $form->handleRequest($request);
            //on stock l'image du vehicule à mettre à jour
            $image=$vehicule->getPhoto();

            // si le formulaire est soumis et validé
            if ($form->isSubmitted() && $form->isValid())
            {
                if($form->get('imageForm')->getData())
                {
                    //on récupére l'image du formulaire
                    $imageFile=$form->get('imageForm')->getData();

                    //on cree un nouveu nom pour la nouvelle image
                    $fileName = $slugger->slug($vehicule->getTitre()) . uniqid() . '.' . $imageFile->guessExtension();

                    //on deplace l'image dans le dossier parametré dans service.yaml
                    try{
                        $imageFile->move($this->getParameter('photos_vehicules'), $fileName);
                    }catch(FileException $e){
                        //gestion des erreurs upload
                    }
                    $vehicule->setPhoto($fileName);
                    //on récupère le manager de doctrine
                }       
                $manager = $doctrine->getManager();
                // on persist l'objet
                $manager->persist($vehicule);
                // puis envoie en bdd
                $manager->flush();
                $this->addFlash('success', 'Le vehicule a bien été mis à jour!');
                return $this->redirectToRoute("admin_app_vehicules");
            }

        return $this->render("vehicule/admin/formulaire.html.twig", [
            'formVehicule'=> $form->createView()
        ]);
    }

/////////////ROUTES ADMIN DELETE VEHICULES///////////////

// /**
// * @Route("admin/delete-vehicule_{id<\d+>}", name="admin_vehicule_delete")
// */
    // public function delete($id, ManagerRegistry $doctrine){
    // // on recupére le vehicule à supprimer
    // $vehicule = $doctrine->getRepository(Vehicule::class)->find($id);
    // // on recupére le manager de doctrine
    // $manager =$doctrine->getManager(); 
    // // on prepare la suppression de vehicule
    // $manager->remove($vehicule);
    // //on execute la suppression dans la BDD
    // $manager->flush();
    
    // return $this->redirectToRoute("admin_app_vehicules");
    // }

////////////////// autre route pour suppression///////////////

/**
* @Route("admin/delete-vehicule_{id<\d+>}", name="admin_vehicule_delete")
*/

    public function delete($id, VehiculeRepository $repo)
    {
        //code pour vérifier l'autentification, si l'user n'est pas connecté
     if( !$this->isGranted('IS_AUTHENTICATED_FULLY')){
         $this->addFlash('error', "Veuillez vous connecter pour acceder à cette page!");
         return $this->redirectToRoute('app_login');
        }
        // si l'utilisateur est connecté mais n'est pas admin
    if(!$this->isGranted("ROLE_ADMIN")){
        $this->addFlash('error', "Vous êtes un intrus, vous ne pouvez pas vous connecter à cette page!!!");
        return $this->redirectToRoute('app_home');
    }
        $vehicule = $repo->find($id);
        $repo->remove($vehicule,1);
        $this->addFlash('success', 'Le vehicule a bien été supprimé!');

        return $this->redirectToRoute('admin_app_vehicules');
    }

////////////////ROUTE ADMIN/CLIENT POUR FICHE D'UN VEHICULE////////////////
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

