<?php

namespace kun391\paypal;

use PayPal\Service\AdaptiveAccountsService;
use PayPal\Types\AA\AccountIdentifierType;
use PayPal\Types\AA\GetVerifiedStatusRequest;
use PayPal\PayPalAPI\MassPayReq;
use PayPal\CoreComponentTypes\BasicAmountType;
use PayPal\PayPalAPI\MassPayRequestItemType;
use PayPal\PayPalAPI\MassPayRequestType;
use PayPal\Service\PayPalAPIInterfaceServiceService;
use yii\base\Component;

class ClassicAPI extends Component
{
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
            $this->pathFileConfig = __DIR__ . '/config-classic.php';
        }

        if (!file_exists(__DIR__ . '/config-classic.php')) {
            throw new \Exception("File config does not exist.", 500);
        }

        $this->_credentials = require($this->pathFileConfig);

        if (!in_array($this->_credentials['mode'], ['sandbox', 'live'])) {
            throw new \Exception("Error Processing Request", 503);
        }

        return $this->_credentials;
    }

    /**
     * @param  $params
     * @return mixed
     */
    public function getAccountInfo(array $params = [])
    {
        if (!$params) {
            return false;
        }

        $getVerifiedStatus = new GetVerifiedStatusRequest();
        $accountIdentifier = new AccountIdentifierType();

        $accountIdentifier->emailAddress      = $params['email'];
        $getVerifiedStatus->accountIdentifier = $accountIdentifier;

        $getVerifiedStatus->firstName       = $params['firstName'];
        $getVerifiedStatus->lastName        = $params['lastName'];
        $getVerifiedStatus->matchCriteria   = 'NAME';
        $getVerifiedStatus->requestEnvelope = [
            'errorLanguage' => 'en_US',
            'detailLevel'   => 'ReturnAll',
        ];

        $service = new AdaptiveAccountsService($this->_credentials);
        try {
            $response = $service->GetVerifiedStatus($getVerifiedStatus);
        } catch (Exception $ex) {
            return false;
        }

        return $response;
    }

    public function sendMoney(array $params = [])
    {
        if (!$params) {
            return false;
        }

        $massPayReq = new MassPayReq();
        $massPayItemArray = array();

        $amount = new BasicAmountType("USD",$params['balance']);
        $massPayRequestItem = new MassPayRequestItemType($amount);
        $massPayRequestItem->ReceiverEmail = $params['email'];

        $massPayRequest = new MassPayRequestType($massPayRequestItem);
        $massPayReq->MassPayRequest = $massPayRequest;

        $service = new PayPalAPIInterfaceServiceService($this->_credentials);
        try {
            $response = $service->MassPay($massPayReq);
        } catch (Exception $ex) {
            return false;
        }

        return $response;
    }
}
