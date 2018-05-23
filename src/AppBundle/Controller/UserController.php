<?php

namespace AppBundle\Controller;

use AppBundle\Form\WalletType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class UserController extends Controller
{
    protected $accountOnline = false;
    protected $noteTotal = 8;
    protected $note = 0;
    protected $etapes = [];

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        return $this->render('default/index.html.twig', []);
    }

    /**
     * @Route("/register/proprietaire", name="new-wallet-proprietaire")
     */
    public function registerWalletProprietaireAction(Request $request)
    {
        // Instantiation des sessions
        $session = $request->getSession();

        // Check si le propriétaire possède déjà un compte en sessions
        if ($session->has("wallet") && $session->has("email")) {
            // Si c'est le cas on cherche son wallet
            $getWalletDetail = $this->container->get('lemonway_sdk.api')
                ->GetWalletDetails(array(
                    'wallet' => $session->get("wallet"),
                ));

            // Réponse
            if (isset($getWalletDetail->lwError)) {
                return new Response('Error, code ' . $getWalletDetail->lwError->CODE . ' : ' . $getWalletDetail->lwError->MSG);
            } else {

                if (!empty($getWalletDetail->wallet->ID)): $this->note++; else: $this->etapes = array_merge(["ID"], $this->etapes); endif;
                if (!empty($getWalletDetail->wallet->BAL)): $this->note++; else: $this->etapes = array_merge(["BALANCE"], $this->etapes); endif;
                if (!empty($getWalletDetail->wallet->NAME)): $this->note++; else: $this->etapes = array_merge(["NAME"], $this->etapes); endif;
                if (!empty($getWalletDetail->wallet->EMAIL)): $this->note++; else: $this->etapes = array_merge(["EMAIL"], $this->etapes); endif;
                if (!empty($getWalletDetail->wallet->kycDocs)): $this->note++; else: $this->etapes = array_merge(["DOCUMENTS"], $this->etapes); endif;
                if (!empty($getWalletDetail->wallet->ibans)): $this->note++; else: $this->etapes = array_merge(["IBANS"], $this->etapes); endif;
                if (!empty($getWalletDetail->wallet->sddMandates)): $this->note++; else: $this->etapes = array_merge(["SDD MANDATES"], $this->etapes); endif;
                if (!empty($getWalletDetail->wallet->cards)): $this->note++; else: $this->etapes = array_merge(["CARDS"], $this->etapes); endif;

                return $this->render('user/wallet.html.twig', [
                    'formWallet' => null,
                    'account' => $getWalletDetail->wallet,
                    'noteFinal' => round(number_format(($this->note * 100 / $this->noteTotal), 2, ',', ''), PHP_ROUND_HALF_UP),
                    'etapesManquantes' => $this->etapes
                ]);
            }
        } else {
            // Génération du form création
            $formWallet = $this->createForm(WalletType::class, null);
            $formWallet->handleRequest($request);
        }

        // Soumission du formulaire
        if ($formWallet->isSubmitted() && $formWallet->isValid()) {
            // Création du Wallet
            $createWallet = $this->container->get('lemonway_sdk.api')
                ->RegisterWallet(array(
                        'wallet' => time(),
                        'clientMail' => $formWallet->getData()['clientEmail'],
                        'clientFirstName' => $formWallet->getData()['clientFirstname'],
                        'clientLastName' => $formWallet->getData()['clientLastname'],
                        'ctry' => $formWallet->getData()['clientCtry'],
                        'mobileNumber' => $formWallet->getData()['clientMobileNumber'],
                        'birthdate' => $formWallet->getData()['clientBirthdate']->format('d/m/Y'),
                        'isCompany' => 0,
                        'nationality' => $formWallet->getData()['clientCtry'] // Répétition du pays de résidence ISO-3
                    )
                );

            // Réponse
            if (isset($createWallet->lwError)) {
                return new Response('Error, code ' . $createWallet->lwError->CODE . ' : ' . $createWallet->lwError->MSG);
            } else {
                $session->set("wallet", json_decode($createWallet->wallet->ID));
                $session->set("email", $formWallet->getData()['clientEmail']);

                $this->addFlash('success', 'Votre wallet a bien été créé (ID: ' . $session->get('wallet') . ')');
                return $this->redirectToRoute('new-wallet-proprietaire');
            }
        }

        return $this->render('user/wallet.html.twig', [
            'formWallet' => $formWallet->createView()
        ]);
    }

}
