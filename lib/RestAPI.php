<?php

namespace kun391\paypal;

use PayPal\Api\Address;
use PayPal\Api\Amount;
use PayPal\Api\BillingInfo;
use PayPal\Api\Currency;
use PayPal\Api\Invoice;
use PayPal\Api\InvoiceItem;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\MerchantInfo;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use yii\base\Component;

class RestAPI extends Component
{
    public $_apiContext;
    public $_credentials;

    public $successUrl = '';
    public $cancelUrl = '';

    public $pathFileConfig;

    /**
     * @param  $config
     *
     * @return mixed
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        //set config default for paypal
        if (!$this->pathFileConfig) {
            $this->pathFileConfig = __DIR__.'/config-rest.php';
        }

        // check file config already exist.
        if (!file_exists($this->pathFileConfig)) {
            throw new \Exception('File config does not exist.', 500);
        }

        //set config file
        $this->_credentials = require $this->pathFileConfig;

        if (!in_array($this->_credentials['config']['mode'], ['sandbox', 'live'])) {
            throw new \Exception('Error Processing Request', 503);
        }

        return $this->_credentials;
    }

    /**
     * Get api context.
     *
     * @return mixed
     */
    public function getConfig()
    {
        if (!$this->_apiContext) {
            $this->setConfig();
        }

        return $this->_apiContext;
    }

    private function setConfig()
    {
        // ### Api context
        // Use an ApiContext object to authenticate
        // API calls. The clientId and clientSecret for the
        // OAuthTokenCredential class can be retrieved from
        // developer.paypal.com
        $this->_apiContext = (new ApiContext(new OAuthTokenCredential(
                $this->_credentials['client_id'],
                $this->_credentials['secret'])
        ));

        $this->_apiContext->setConfig($this->_credentials['config']);

        return $this->_apiContext;
    }

    private function getBaseUrl()
    {
        if (PHP_SAPI == 'cli') {
            $trace = debug_backtrace();
            $relativePath = substr(dirname($trace[0]['file']), strlen(dirname(dirname(__FILE__))));
            echo 'Warning: This sample may require a server to handle return URL. Cannot execute in command line. Defaulting URL to http://localhost$relativePath \n';

            return 'http://localhost'.$relativePath;
        }
        $protocol = 'http';
        if ($_SERVER['SERVER_PORT'] == 443 || (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on')) {
            $protocol .= 's';
        }
        $host = $_SERVER['HTTP_HOST'];
        $request = $_SERVER['PHP_SELF'];

        return dirname($protocol.'://'.$host.$request);
    }

    public function createInvoice($params = null)
    {
        if (!$params) {
            return false;
        }

        $invoice = new Invoice();
        // ### Invoice Info
        // Fill in all the information that is
        // required for invoice APIs
        $invoice
            ->setMerchantInfo(new MerchantInfo())
            ->setBillingInfo([new BillingInfo()]);

        // ### Merchant Info
        // A resource representing merchant information that can be
        // used to identify merchant
        $owner_email = $this->_credentials['business_owner'];

        $invoice->getMerchantInfo()->setEmail($owner_email);

        // ### Billing Information
        // Set the email address for each billing
        $billing = $invoice->getBillingInfo();
        $billing[0]->setEmail($params['email']);

        $items = [];
        foreach ($params['items'] as $key => $item) {
            // code...
            $items[$key] = new InvoiceItem();
            $items[$key]
                ->setName($item['name'])
                ->setQuantity($item['quantity'])
                ->setUnitPrice(new Currency());
            $items[$key]->getUnitPrice()
                ->setCurrency($params['currency'])
                ->setValue((float) $item['price']);
        }
        // ### Items List
        $invoice->setItems($items);
        $request = clone $invoice;

        $errors = [];
        try {
            $invoice->create($this->config);
        } catch (PayPal\Exception\PayPalConnectionException $ex) {
            $errors = [
                'code'    => $ex->getCode(),
                'data'    => $ex->getData(),
                'message' => $ex->getMessage(),
            ];
        } catch (\Exception $ex) {
            $errors = [
                'code'    => $ex->getCode(),
                'message' => $ex->getMessage(),
            ];
        }

        return [
            'errors'    => $errors,
            'invoices'  => $invoice,
        ];
    }

    public function getLinkCheckOut($params = null)
    {
        if (!$params) {
            return false;
        }

         /*Payer
         A resource representing a Payer that funds a payment
         For paypal account payments, set payment method
         to 'paypal'.
         */
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $itemList = new ItemList();
        // Item must be a array and has one or more item.
        if (!$params['items'] || !isset($params['total_price'])) {
            return false;
        }
        $errors = [];
        $arrItem = [];
        foreach ($params['items'] as $key => $item) {
            $it = new Item();
            $it->setName($item['name'])
                ->setCurrency($params['currency'])
                ->setQuantity($item['quantity'])
                ->setPrice($item['price']);
            $arrItem[] = $it;
        }
        $itemList->setItems($arrItem);

        $amount = new Amount();
        $amount->setCurrency($params['currency'])
               ->setTotal($params['total_price']);

        $transaction = new Transaction();
        $transaction->setAmount($amount)
                    ->setItemList($itemList)
                    ->setDescription($params['description']);

        // ### Redirect urls
        // Set the urls that the buyer must be redirected to after
        // payment approval/ cancellation.
        $redirectUrls = new RedirectUrls();

        $baseUrl = $this->getBaseUrl();

        try {
            $redirectUrls->setReturnUrl($baseUrl.$this->successUrl)
                         ->setCancelUrl($baseUrl.$this->cancelUrl);
        } catch (\InvalidArgumentException $ex) {
            return [
                'errors'        => [
                    'code'    => $ex->getCode(),
                    'message' => $ex->getMessage(),
                ],
            ];
        }
        // ### Payment
        // A Payment Resource; create one using
        // the above types and intent set to 'sale'
        $payment = new Payment();
        $payment->setIntent('sale')
                ->setPayer($payer)
                ->setRedirectUrls($redirectUrls)
                ->setTransactions([$transaction]);

        // ### Create Payment
        // Create a payment by calling the 'create' method
        // passing it a valid apiContext.
        try {
            $payment->create($this->config);
            // ### Get redirect url
            $redirectUrl = $payment->getApprovalLink();
        } catch (PayPal\Exception\PayPalConnectionException $ex) {
            $errors = [
                'code'    => $ex->getCode(),
                'data'    => $ex->getData(),
                'message' => $ex->getMessage(),
            ];
        } catch (\Exception $ex) {
            $errors = [
                'code'    => $ex->getCode(),
                'message' => $ex->getMessage(),
            ];
        }

        return [
            'errors'        => $errors,
            'payment_id'    => $payment->getId(),
            'status'        => $payment->getState(),
            'description'   => $transaction->getDescription(),
            'redirect_url'  => isset($redirectUrl) ? $redirectUrl : null,
        ];
    }

    public function getResult($paymentId)
    {
        $payment = Payment::get($paymentId, $this->config);

        $execution = new PaymentExecution();
        $execution->setPayerId($_GET['PayerID']);
        $payment = $payment->execute($execution, $this->config);

        $result = @$payment->toArray();

        return $result;
    }
}
