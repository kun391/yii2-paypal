<?php

namespace kun391\paypal;

use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Invoice;
use PayPal\Api\MerchantInfo;
use PayPal\Api\BillingInfo;
use PayPal\Api\InvoiceItem;
use PayPal\Api\Phone;
use PayPal\Api\Address;
use PayPal\Api\Currency;
use yii\base\Component;

class RestAPI extends Component
{
    public $_apiContext;
    public $_credentials;

    public $pathFileConfig;
    /**
     * @param  $config
     * @return mixed
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        if (!$this->pathFileConfig) {
            $this->pathFileConfig = __DIR__ . '/config-rest.php';
        }
        // check file config already exist.
        if (!file_exists(__DIR__ . '/config-rest.php')) {
            throw new \Exception("File config does not exist.", 500);
        }

        $this->_credentials = require($this->pathFileConfig);

        if (!in_array($this->_credentials['config']['mode'], ['sandbox', 'live'])) {
            throw new \Exception("Error Processing Request", 503);
        }

        return $this->_credentials;
    }

    /**
     * Get api context
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

    public function createInvoice($attributes = null)
    {
        if (!$attributes) {
            return false;
        }

        $invoice = new Invoice();

        // ### Invoice Info
        // Fill in all the information that is
        // required for invoice APIs
        $invoice
            ->setMerchantInfo(new MerchantInfo())
            ->setBillingInfo(array(new BillingInfo()));

        // ### Merchant Info
        // A resource representing merchant information that can be
        // used to identify merchant
        $owner_email = $this->_credentials['business_owner'];

        $invoice->getMerchantInfo()->setEmail($owner_email);

        // ### Billing Information
        // Set the email address for each billing
        $billing = $invoice->getBillingInfo();
        $billing[0]->setEmail('nguyentruongthanh.dn@gmail.com');

        // ### Items List
        $items = array();
        $items[0] = new InvoiceItem();

        $items[0]
            ->setName("Product Transaction")->setQuantity(2)->setUnitPrice(new Currency());

        $items[0]->getUnitPrice()->setCurrency('USD')->setValue(200);

        $invoice->setItems($items);
        // For Sample Purposes Only.
        $request = clone $invoice;

        try {
            $invoice->create($this->config);
        } catch (\Exception $ex) {
            return $ex;
        }

        return $invoice;
    }

}
