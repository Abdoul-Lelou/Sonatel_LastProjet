<?php

namespace App\Controller;

use App\Entity\Appointement;
use App\Entity\Patient;
use App\Entity\User;
use App\Repository\AppointementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints\DateTime;
// //use 'Vendor\autoload.php;
// use Symfony\Component\HttpClient;
//  use  '/vendor/autoload.php';
// use Twilio\Rest\Client;
// use __DIR__ '/vendor/autoload.php';


/**
 * @Route("/api")
 */
class AppointementController extends AbstractController
{
    /**
     * @Route("/appointement", methods={"GET"})
     */
    public function findAppointement(AppointementRepository $appointementRepository)
    {
        //AVOIR AU MOINS LE ROLE_SECRETAIRE POUR RECUPERER LES  APPOINTEMENT
        $this->denyAccessUnlessGranted('ROLE_SECRETAIRE', null, ' Accès non autorisé');

        return $this->json($appointementRepository->findAll(), 200, [], ['groups' => 'patient']);
    }

    /**
     * @Route("/appointement/medecin", methods={"GET"})
     */
    public function findAppointementByMedecin(AppointementRepository $appointementRepository)
    {
        //AVOIR AU MOINS LE ROLE_SECRETAIRE POUR RECUPERER LES  APPOINTEMENT
        $this->denyAccessUnlessGranted('ROLE_SECRETAIRE', null, ' Accès non autorisé');

        return $this->json($this->getUser()->getAppointements(), 200, [], ['groups' => 'patient']);
    }

    /**
     * @Route("/appointement/{id}", methods={"GET"})
     */
    public function findAppointementByid($id, AppointementRepository $appointementRepository)
    {
        //AVOIR AU MOINS LE ROLE_SECRETAIRE POUR RECUPERER UN APPOINTEMENT
        $this->denyAccessUnlessGranted('ROLE_SECRETAIRE', null, 'Accès non autorisé');

        return $this->json($appointementRepository->find($id), 200, [], ['groups' => 'patient']);
    }

    /**
     * @Route("/appointement", name="appointement",methods={"POST"})
     */
    public function addappointement(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        //AVOIR AU MOINS LE ROLE_SECRETAIRE POUR AJOUTER UN APPOINTEMENT
        $this->denyAccessUnlessGranted('ROLE_SECRETAIRE', null, 'Accès non autorisé');

        $values = json_decode($request->getContent());

        if (isset($values->motif,$values->date,$values->patient,$values->heureFin,$values->heureDebut)) {
            try {
                $medecin_id = intval(preg_replace('~[^0-9]~', '', $values->medecin));
                $medecin = $entityManager->getRepository(User::class)->find(intval($medecin_id));
                $patient_id = intval(preg_replace('~[^0-9]~', '', $values->patient));
                $patient = $entityManager->getRepository(Patient::class)->find(intval($patient_id));

                $appointement = new Appointement();

                $appointement->setMotif($values->motif);
                $appointement->setDate(str_replace('-', '/', $values->date));
                $appointement->setPatient($patient);
                $appointement->setMedecin($medecin);
                $appointement->setHeureDebut($values->heureDebut);
                $appointement->setHeureFin($values->heureFin);
                $appointement->setIsEnabled(true);

                $erreur = $validator->validate($appointement);
                if (count($erreur) > 0) {
                    return $this->json(['status' => 400, 'message' => 'Donnees saisie incorrect']);
                }
                $entityManager->persist($appointement);
                $entityManager->flush();

                // $id = 'AC6c91208078397e7639ab2e39238f6cb3';
                // $token = '952f3c4d4f287dc8ac2b5a376fbdbae1';
                // $phone = '00221778719986';

                // $client = new Twilio\Rest\Client($id, $token);
                // $message = $client->message->create(
                //     $tele, [
                //         'from' => '+15803076220',
                //         'body' => 'vous avez un rendez-vous',
                //     ]
                //     );
                // if ($message->sid) {
                //     return 'voilas';
                // }

                
                    // $user = $this->getUser();
                    // $authy_api = new \Authy\AuthyApi( getenv('TWILIO_AUTHY_API_KEY') );
                    // $user      = $authy_api->registerUser( 'abdourahmane.benzy@gmail.com', $phone, '+221' );

                    // dd($user);die;
              
                    // if ( $user->ok() ) {
              
                    //     $sms = $authy_api->requestSms( $user->id(), [ "force" => "true" ] );
              
                       
              
                    //     $user_params = [
                    //         'email' => $email,
                    //         'countryCode' => '+221',
                    //         'phone' => $phone,
                    //         'authy_id' => $id,
                    //     ];
                    //     return $this->json("ok ca marche",201,[])
                    //   //  $this->get('session')->set('user', $user_params);
                    // }
               

                return $this->json($appointement, 201, [], ['groups' => 'patient']);
            } catch (NotEncodableValueException $e) {
                return $this->json([
                        'status' => 400,
                        'message' => $e->getMessage(),
                    ]);
            }
        } else {
            return $this->json(['status' => 400, 'message' => 'Remplissez tout les donnees']);
        }

        return $this->json(['status' => 400, 'message' => ' oups quelque chose ne va pas']);
    }

    /**
     * @Route("/appointement/{id}", name="appointdelete", methods={"DELETE"})
     */
    public function deleteAppointement(EntityManagerInterface $entityManager, $id, AppointementRepository $appointementRepository)
    {
        //AVOIR AU MOINS LE ROLE_SECRETAIRE POUR SUPPRIMER UN APPOINTEMENT
        $this->denyAccessUnlessGranted('ROLE_SECRETAIRE', null, 'Accès non autorisé ');

        $appointement = $appointementRepository->find($id);

        try {
            if ($appointement == null) {
                return $this->json(['status' => 400, 'message' => "Ce rendez-vous n'existe pas"]);
            }

            $entityManager->remove($appointement);
            $entityManager->flush();

            return $this->json(['status' => 400, 'message' => 'Rendez-vous supprime']);
        } catch (NotEncodableValueException $e) {
            return $this->json(['status' => 400, 'message' => $e->getMessage()]);
        }

        return $this->json(['status' => 400, 'message' => "Ce Rendez-vous n'existe pas"]);
    }

    /**
     * @Route("/appointement/{id}", methods={"PUT"})
     */
    public function updateAppointement(EntityManagerInterface $entityManager, $id, AppointementRepository $appointementRepository, Request $request, ValidatorInterface $validator)
    {
        //AVOIR AU MOINS LE ROLE_SECRETAIRE POUR MODIFIER UN APPOINTEMENT
        $this->denyAccessUnlessGranted('ROLE_SECRETAIRE', null, 'Accès non autorisé');

        $appointement = $appointementRepository->find($id);
        $values = json_decode($request->getContent());

        if ($appointement && isset($values->motif,$values->date,$values->medecin,$values->patient,$values->heureFin,$values->heureDebut)) {
            try {
                $medecin_id = intval(preg_replace('~[^0-9]~', '', $values->medecin));
                $medecin = $entityManager->getRepository(User::class)->find(intval($medecin_id));

                foreach ($appointementRepository->findAll() as  $value) {
                    if ($values->date == $value->date && $medecin == $value->medecin && $values->heureDebut == $value->heureDebut && $value->isEnabled && $appointement->getId() != $value->getId()) {
                        return $this->json(['status' => '400', 'message' => 'Rendez-vous déjà réservé']);
                    }
                }

                $appointement->setMotif($values->motif);
                $appointement->setDate(str_replace('-', '/', $values->date));
                $appointement->setHeureFin($values->heureFin);
                $appointement->setHeureDebut($values->heureDebut);
                $appointement->setMedecin($medecin);

                $erreur = $validator->validate($appointement);
                if (count($erreur) > 0) {
                    return $this->json(['status' => 400, 'message' => 'Donnees saisie incorrect']);
                }
                $entityManager->persist($appointement);
                $entityManager->flush();

                return $this->json($appointement, 201, [], ['groups' => 'patient']);
            } catch (NotEncodableValueException $e) {
                return $this->json([
                        'status' => 400,
                        'message' => $e->getMessage(),
                    ]);
            }
        } else {
            return $this->json(['status' => 400, 'message' => 'Remplissez tout les donnees']);
        }

        return $this->json(['status' => 400, 'message' => " Ce Rendez-vous n'existe pas"]);
    }

    /**
     * @Route("/appointement/status/{id}", methods={"PUT"})
     */
    public function statusAppointement($id, AppointementRepository $appointementRepository, EntityManagerInterface $entityManager)
    {
        //AVOIR AU MOINS LE ROLE_SECRETAIRE POUR RECUPERER UN APPOINTEMENT
        $this->denyAccessUnlessGranted('ROLE_SECRETAIRE', null, 'Accès non autorisé');

        $appointement = $appointementRepository->find($id);
        // $date = new \DateTime();
        // var_dump($date->getFullYear());die;
        // $today = $date.getFullYear().'/'.$date.getMonth().'/'.$date.getDay();
        if ($appointement !== null) {
            try {
                if ($appointement->getIsEnabled() === false) {
                    foreach ($appointementRepository->findAll() as $value) {
                        if ($appointement->getId() != $value->getId() && $appointement->heureDebut == $value->heureDebut && str_replace('-', '/', $appointement->date) == str_replace('-', '/', $value->date) && $appointement->medecin == $value->medecin && $value->isEnabled ) {
                            return $this->json(['status' => 200, 'message' => 'rendez-vous déjà réservé']);
                        }
                        // elseif ($appointement->getId() != $value->getId()  && str_replace('-', '/', $appointement->date) == $today  && $value->isEnabled ) {
                        //     return $this->json(['status' => 200, 'message' => 'rendez-vous déjà expiré']);
                        // }
                    }

                    $appointement->setIsEnabled(true);
                    $entityManager->persist($appointement);
                    $entityManager->flush();

                    return $this->json(['status' => 200, 'message' => ' Rendez-vous réactivé']);
                } elseif ($appointement->getIsEnabled() === true) {
                    $appointement->setIsEnabled(false);
                    $entityManager->persist($appointement);
                    $entityManager->flush();

                    return $this->json(['status' => 200, 'message' => ' Rendez-vous annulé']);
                }
            } catch (NotEncodableValueException $e) {
                return $this->json([
                                'status' => 400,
                                'message' => $e->getMessage(),
                            ]);
            }
        }

        return $this->json(['status' => 400, 'message' => " Ce rendez-vous n'existe pas"]);
    }
}
