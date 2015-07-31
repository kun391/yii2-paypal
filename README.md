# yii2-paypal
Process with paypal for Yii2
=========

Installation
====

Add to the composer.json file following section:

```
php composer.phar require --prefer-dist kun391/yii2-paypal:"*"
```

```
"kun391/yii2-paypal":"dev-master"
```

Add to to you Yii2 config file this part with component settings:

- Create file config.php for RESTAPI and config.php for Classic API every where:
- Add to config to component of file main.php:
```php
...
'payPalClassic'    => [
    'class'        => 'kun391\paypal\ClassicAPI',
    'pathFileConfig' => ''
],
'payPalRest'               => [
    'class'        => 'kun391\paypal\RestAPI',
    'pathFileConfig' => ''
],
...
```
