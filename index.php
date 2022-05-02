<?php
include_once 'vendor\autoload.php';
use Crazysnowflake\PayopTest\PayopTest;
use GuzzleHttp\Exception\GuzzleException;

$order      = array(
    'id'          => 'test-order',
    'amount'      => '5',
    'currency'    => 'EUR',
    'items'       =>
        array(
            array(
                'id'    => '487',
                'name'  => 'Item 1',
                'price' => '2.0999999999999996',
            ),
        ),
    'description' => 'string',
);
$customer   = array(
    'email'       => 'test.user@tes.com',
    'phone'       => '',
    'name'        => '',
    'extraFields' => array()
);
$card       = array(
    'pan'            => 5555555555554444,
    'expirationDate' => '12/20',
    'cvv'            => 322,
    'holderName'     => 'Card Holder',
);
$method     = 381;
$public_key = 'application-a61e0463-e737-491c-8b71-bb157ab43bd6';
$secret_key = 'application-a61e0463-e737-491c-8b71-bb157ab43bd6';
$jwt_token  = 'token';

$client = new PayopTest($public_key, $secret_key, $jwt_token);
try {
    $invoiceID = $client->setPaymentMethod($method)
                        ->setInvoiceResultUrl('Some Url')
                        ->setInvoiceFailPath('Some Url')
                        ->createInvoice($order, $customer);

    $card_token = $client->createCardToken($invoiceID, $card);

    $result = $client->setCheckStatusUrl('Some Url')
                     ->checkout($invoiceID, $customer, $card_token['token']);

    $transaction = $client->getTransaction($result['txid']);
    $status      = $client->checkInvoiceStatus($invoiceID);
} catch (GuzzleException $e) {
    var_dump($e->getMessage());
}
