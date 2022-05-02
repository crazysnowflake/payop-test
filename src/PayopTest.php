<?php
namespace crazysnowflake\PayopTest;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\RequestOptions as RequestOptions;
use GuzzleHttp\Exception\GuzzleException;

class PayopTest
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_HEAD = 'HEAD';

    private HttpClient $client;
    private string $api_url = 'https://payop.com/v1/';
    private string $public_key;
    private string $secret_key;
    private string $jwt_token;
    private string $check_status_url = '';
    private string $invoice_result_url = '';
    private string $invoice_fail_path = '';
    private string $currency = 'EUR';
    private int $payment_method = 381;

    public function __construct(
        string $public_key,
        string $secret_key,
        string $jwt_token
    ) {
        $this->client     = new HttpClient(['base_uri' => $this->api_url]);
        $this->public_key = $public_key;
        $this->secret_key = $secret_key;
        $this->jwt_token  = $jwt_token;
    }

    /**
     * @param  array  $order
     * @param  array  $payer
     * @param  array  $metadata
     *
     * @return string
     * @throws GuzzleException
     *
     */
    public function createInvoice(
        array $order,
        array $payer,
        array $metadata = []
    ): string {
        $params   = [
            'publicKey'     => $this->public_key,
            'order'         => $order,
            'payer'         => $payer,
            'metadata'      => $metadata,
            'language'      => 'en',
            'paymentMethod' => $this->getPaymentMethod(),
            'resultUrl'     => $this->getInvoiceResultUrl(),
            'failPath'      => $this->getInvoiceFailPath(),
            'signature'     => $this->generateSignature($order),
        ];
        $response = $this->client->request(
            self::METHOD_POST,
            'invoices/create',
            [RequestOptions::JSON => $params]
        );

        return json_decode($response->getBody()->getContents(), true)['data'];
    }

    /**
     * @param  string  $invoice_id
     *
     * @return array
     * @throws GuzzleException
     */
    public function getInvoice(string $invoice_id): array
    {
        $response = $this->client->request(
            self::METHOD_GET,
            "invoices/{$invoice_id}"
        );

        return json_decode($response->getBody()->getContents(), true)['data'];
    }

    public function checkInvoiceStatus(string $invoice_id): array
    {
        $response = $this->client->request(
            self::METHOD_GET,
            "checkout/check-transaction-status/{$invoice_id}"
        );

        return json_decode($response->getBody()->getContents(), true)['data'];
    }

    /**
     * @param  string  $invoice_id
     * @param  array  $customer
     * @param  string  $cardToken
     *
     * @return array
     * @throws GuzzleException
     *
     */
    public function checkout(string $invoice_id, array $customer, string $cardToken = ''): array
    {
        $params = [
            'invoiceIdentifier' => $invoice_id,
            'customer'          => $customer,
            'cardToken'         => $cardToken,
            'payCurrency'       => $this->getCurrency(),
            'paymentMethod'     => $this->getPaymentMethod(),
            'checkStatusUrl'    => $this->getCheckStatusUrl(),
        ];

        $response = $this->client->request(
            self::METHOD_POST,
            'checkout/create',
            [RequestOptions::JSON => $params]
        );

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param  string  $txid
     *
     * @return array
     * @throws GuzzleException
     */
    public function getTransaction(string $transaction_id): array
    {
        $response = $this->client->request(
            self::METHOD_GET,
            "transactions/{$transaction_id}",
            [RequestOptions::HEADERS => ['Authorization' => $this->jwt_token]]
        );

        return json_decode($response->getBody()->getContents(), true)['data'];
    }

    /**
     * @param  string  $invoice_id
     * @param  array  $card
     *
     * @return array
     * @throws GuzzleException
     */
    public function createCardToken(string $invoice_id, array $card): array
    {
        $card['invoiceIdentifier'] = $invoice_id;

        $response = $this->client->request(
            self::METHOD_POST,
            'payment-tools/card-token/create',
            [RequestOptions::JSON => $card]
        );

        return json_decode($response->getBody()->getContents(), true);
    }


    /**
     * @param  array  $order
     *
     * @return string
     */
    private function generateSignature(array $order): string
    {
        $dataSet = [
            'id' => $order['id'],
            'amount' => $order['amount'],
            'currency' => $order['currency']
        ];
        ksort($dataSet, SORT_STRING);
        $dataSet   = array_values($dataSet);
        $dataSet[] = $this->secret_key;
        return hash('sha256', implode(':', $dataSet));
    }

    /**
     * @return string
     */
    public function getCheckStatusUrl(): string
    {
        return $this->check_status_url;
    }

    /**
     * @param  string  $url
     *
     * @return $this
     */
    public function setCheckStatusUrl(string $url): PayopTest
    {
        $this->check_status_url = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getInvoiceResultUrl(): string
    {
        return $this->invoice_result_url;
    }

    /**
     * @param  string  $url
     *
     * @return $this
     */
    public function setInvoiceResultUrl(string $url): PayopTest
    {
        $this->invoice_result_url = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getInvoiceFailPath(): string
    {
        return $this->invoice_fail_path;
    }

    /**
     * @param  string  $url
     *
     * @return $this
     */
    public function setInvoiceFailPath(string $url): PayopTest
    {
        $this->invoice_fail_path = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @param  string  $currency
     *
     * @return $this
     */
    public function setCurrency(string $currency): PayopTest
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return int
     */
    public function getPaymentMethod(): int
    {
        return $this->payment_method;
    }

    /**
     * @param  int  $method
     *
     * @return $this
     */
    public function setPaymentMethod(int $method): PayopTest
    {
        $this->payment_method = $method;

        return $this;
    }
}
