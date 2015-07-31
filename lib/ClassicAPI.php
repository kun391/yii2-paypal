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
    public $mode;
    public $userName;
    public $password;
    public $signature;
    public $_credentials;

    public $setting = [
        'acct1.UserName'  => 'nguyentruongthanh.dn-facilitator-1_api1.gmail.com',
        'acct1.Password'  => 'GRHYUV2DJHNBFTAA',
        'acct1.Signature' => 'APP9kKh6roKmPNKj6yBK5oSwdD39ADujX4sfPXjr.hGf1wjRi1THwoVq',
        'mode'            => 'sandbox',
    ];

    /**
     * @param  $config
     * @return mixed
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        if (!in_array($this->mode, ['sandbox', 'live'])) {
            throw new \Exception("Error Processing Request", 503);
        }

        if (!$this->mode || !$this->userName || !$this->password || !$this->signature) {
            $this->_credentials = $this->setting;
        } else {
            $this->_credentials = [
                'acct1.UserName'  => $this->userName,
                'acct1.Password'  => $this->password,
                'acct1.Signature' => $this->signature,
                'mode'            => $this->mode,
            ];
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
