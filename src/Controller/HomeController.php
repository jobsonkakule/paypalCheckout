<?php
namespace App\Controller;

use App\Entity\Basket;
use App\Entity\TransactionFactory;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Rest\ApiContext;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends AbstractController {

    private $clientId = "xxxxxx";
    private $clientSecret = "xxxxxx";

    /**
     * @Route("/", name="home")
     * @return Response
     */
    public function index(): Response
    {

        // $ch = curl_init();
        // $certificate_location = "/usr/local/openssl-0.9.8/certs/cacert.pem";
        // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $certificate_location);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $certificate_location);

        // dd($payment);
        $products = Basket::fake();
        
        return $this->render('home.html.twig', [
            'products' => $products->getProducts()
        ]);
    }
    

    /**
     * @Route("/pay", name="pay")
     * @return Response
     */
    public function pay(Request $request): Response
    {
        $apiContext = new ApiContext(
            new OAuthTokenCredential(
                $this->clientId,
                $this->clientSecret
            )
        );

        $basket = Basket::fake();
        $payment = Payment::get($request->get('paymentID'), $apiContext);

        $execution = (new PaymentExecution())
            ->setPayerId($request->get('payerID'))
            ->addTransaction(TransactionFactory::fromBasket($basket, 0.2 ));

        try {
            $payment->execute($execution, $apiContext);
            new JsonResponse([
                'id' => $payment->getId()
            ]);
        } catch (PayPalConnectionException $e) {
            return $this->redirect('', 500);
            dump(json_decode($e->getData()));
        }

        return $this->render('pages/checkout.html.twig');
    }

    /**
     * @Route("/checkout", name="checkout")
     */
    public function checkout()
    {
        $basket = Basket::fake();


        $apiContext = new ApiContext(
            new OAuthTokenCredential(
                $this->clientId,
                $this->clientSecret
            )
        );

        $payment = new Payment();
        
        $payment->addTransaction(TransactionFactory::fromBasket($basket));

        $payment->setIntent('sale');
        $redirectUrls = (new RedirectUrls())
            ->setReturnUrl('http://localhost:8000/pay')
            ->setCancelUrl('http://localhost:8000/checkout');
        $payment->setRedirectUrls($redirectUrls);

        $payment->setPayer((new Payer())->setPaymentMethod('paypal'));

        try {
            $payment->create($apiContext);
            return new JsonResponse([
                'id' => $payment->getId()
            ]);
 
        } catch(PayPalConnectionException $e) {
            dump(json_decode($e->getData()));
        }
        
        // return $this->render('pages/checkout.html.twig');
    }
}