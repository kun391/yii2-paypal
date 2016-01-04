Process Payment with paypal for Yii2
=========

[![Build Status](https://travis-ci.org/kun391/yii2-paypal.svg?branch=master)](https://travis-ci.org/kun391/yii2-paypal)
[![Latest Stable Version](https://poser.pugx.org/kun391/yii2-paypal/v/stable)](https://packagist.org/packages/kun391/yii2-paypal)
[![Total Downloads](https://poser.pugx.org/kun391/yii2-paypal/downloads)](https://packagist.org/packages/kun391/yii2-paypal)
[![Monthly Downloads](https://poser.pugx.org/kun391/yii2-paypal/d/monthly)](https://packagist.org/packages/kun391/yii2-paypal)
[![Daily Downloads](https://poser.pugx.org/kun391/yii2-paypal/d/daily)](https://packagist.org/packages/kun391/yii2-paypal)

Requirements
====
- PHP >= 5.4
- php5-curl (System)

Installation
====

Add to the composer.json file following section:

```
php composer.phar require --prefer-dist kun391/yii2-paypal:"*"
```

or

```
"kun391/yii2-paypal":"dev-master"
```

Add to to you Yii2 config file this part with component settings:

> Create file config.php for RESTAPI and config.php for Classic API every where:

- ClassicAPI

```php
/**
 * Information PAYPAL's enviroments for classic API
 * @var string
 */

// E.g:
// If enviroment is Development you should use mode = sandbox and endpoint = api.sandbox.paypal.com
// [
//     'acct1.UserName'  => 'nguyentruongthanh.dn-facilitator-1_api1.gmail.com',
//     'acct1.Password'  => 'GRHYUV2DJHNBFTAA',
//     'acct1.Signature' => 'APP9kKh6roKmPNKj6yBK5oSwdD39ADujX4sfPXjr.hGf1wjRi1THwoVq',
//     'mode'            => 'sandbox',
// ];

// E.g:
// If enviroment is live you should use mode = live
// [
//     'acct1.UserName'  => 'nguyentruongthanh.dn-facilitator-1_api1.gmail.com',
//     'acct1.Password'  => 'GRHYUV2DJHNBFTAA',
//     'acct1.Signature' => 'APP9kKh6roKmPNKj6yBK5oSwdD39ADujX4sfPXjr.hGf1wjRi1THwoVq',
//     'mode'            => 'live',
// ];

return  [
    'acct1.UserName'  => 'nguyentruongthanh.dn-facilitator-1_api1.gmail.com',
    'acct1.Password'  => 'GRHYUV2DJHNBFTAA',
    'acct1.Signature' => 'APP9kKh6roKmPNKj6yBK5oSwdD39ADujX4sfPXjr.hGf1wjRi1THwoVq',
    'mode'            => 'sandbox',
];

```

- RestAPI

```php
/**
 * Information PAYPAL's enviroments
 * @var string
 */

// E.g:
// If enviroment is Development you should use mode = sandbox and endpoint = api.sandbox.paypal.com
// $setting = [
//     'endpoint'       => 'api.sandbox.paypal.com',
//     'client_id'      => 'AV92BhCOYzF4Vejrbphu1ksMn4KYSlvbzCTcbLdOMixBvAS7sQZhOvMNkMoG',
//     'secret'         => 'EDdjYm7i8w2XZwWGyTqPfPDJim2dUV1hX_3dhY0fR-HulrENli6043rY_0GO1ro1gnkxVe3bMWNDikvq',
//     'business_owner' => 'nguyentruongthanh.dn-facilitator-1@gmail.com',
// ];

// E.g:
// If enviroment is live you should use mode = live and endpoint = api.paypal.com
// $setting = [
//     'endpoint'       => 'api.paypal.com',
//     'client_id'      => 'AV92BhCOYzF4Vejrbphu1ksMn4KYSlvbzCTcbLdOMixBvAS7sQZhOvMNkMoG',
//     'secret'         => 'EDdjYm7i8w2XZwWGyTqPfPDJim2dUV1hX_3dhY0fR-HulrENli6043rY_0GO1ro1gnkxVe3bMWNDikvq',
//     'business_owner' => 'nguyentruongthanh.dn-facilitator-1@gmail.com',
// ];

$setting = [
    'endpoint'       => 'api.sandbox.paypal.com',
    'client_id'      => 'AX9sEz0g3cCzD_heoGyedx7LKSuEx1Lx7H8aGXIrzQmDhqV-V5bV0sbVFc195mNKbE81OkAPZZi_7dfa',
    'secret'         => 'EDdjYm7i8w2XZwWGyTqPfPDJim2dUV1hX_3dhY0fR-HulrENli6043rY_0GO1ro1gnkxVe3bMWNDikvq',
    'business_owner' => 'nguyentruongthanh.dn-facilitator-1@gmail.com',
];

return \yii\helpers\ArrayHelper::merge(['config' => [
        'mode'                   => 'sandbox',
        'http.ConnectionTimeOut' => 60,
        'log.LogEnabled'         => false,
        'log.FileName'           => '@api/runtime/PayPal.log',
        'log.LogLevel'           => 'FINE',
    ]
], $setting);
```

> Add to config to component of file main.php:

```php
...
'payPalClassic'    => [
    'class'        => 'kun391\paypal\ClassicAPI',
    'pathFileConfig' => '',
],
'payPalRest'               => [
    'class'        => 'kun391\paypal\RestAPI',
    'pathFileConfig' => '',
    'successUrl' => '' //full url action return url
    'cancelUrl' => '' //full url action return url
],
...
```

=========

Usage
====

> REST API

- Create a invoice with paypal

```php
//define params for request 
$params = [
    'currency' => 'Usd', // only support currency same PayPal
    'email' => 'nguyentruongthanh.dn@gmail.com',
    'items' => [
        [
            'name' => 'Vinamilk',
            'quantity' => 2,
            'price' => 100
        ],
        [
            'name' => 'Pepsi',
            'quantity' => 3,
            'price' => 90
        ]
    ]
];

// information invoice response
$response = Yii::$app->payPalRest->createInvoice($params);
```

- Get link checkout on PayPal

```php
//define params for request 
$params = [
    'currency' => 'USD', // only support currency same PayPal
    'description' => 'Buy some item',
    'total_price' => 470,
    'email' => 'nguyentruongthanh.dn@gmail.com',
    'items' => [
        [
            'name' => 'Vinamilk',
            'quantity' => 2,
            'price' => 100
        ],
        [
            'name' => 'Pepsi',
            'quantity' => 3,
            'price' => 90
        ]
    ]
];

$response = Yii::$app->payPalRest->getLinkCheckOut($params);
```

> CLASSIC API

- Get Information Account

```php

Attribute matchCriteria you can specify either NAME or NONE. Default: None
Attribute firstName and lastName (Required) if matchCriteria is name.

$params = [
    'email' => 'nguyentruongthanh.dn@gmail.com',
    'matchCriteria' => 'name',
    'firstName' => 'Thanh',
    'lastName'  => 'Nguyen'
];

$response = Yii::$app->payPalClassic->getAccountInfo($params);
```

- Send money (Mass Pay)

```php
$params = [
    'email' => 'nguyentruongthanh.dn@gmail.com',
    'balance' => 200,
];

$response = Yii::$app->payPalClassic->sendMoney($params);
``
