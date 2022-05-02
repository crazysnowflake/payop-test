## Installation

To install, either run

```
$ php composer.phar require crazysnowflake/payop-test "^1.0@dev"
```

or add

```
"crazysnowflake/payop-test": "^1.0@dev"
```

to the `require` section of your `composer.json` file.

## Usage

```php
use Crazysnowflake\PayopTest\PayopTest;

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
} catch (\GuzzleHttp\Exception\GuzzleException $e) {
}
```

More examples see in index.php

## License

**payop-test** is released under the MIT License. See the bundled `LICENSE.md` for details.
