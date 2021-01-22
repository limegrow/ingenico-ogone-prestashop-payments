Ogone API Client
=======================

# FlexCheckout URL #

```php
use IngenicoClient\Configuration;
use IngenicoClient\Client;
use IngenicoClient\Order;
use IngenicoClient\Alias;

$configuration = new Configuration('pspid', 'api_username', 'password', 'passphrase', 'sha512');
$client = new Client($configuration);
$order = new Order();
$order->setAmount(100);
$order->setCurrency('EUR');
$order->setOrderid('Order1');
$order->setPaymentMethod('CreditCard');
$alias = new Alias('customer1');
$url = $client->getFlexCheckoutUrl($order, $alias);
```

# DirectLink Payment #

```php
use IngenicoClient\Alias;
use IngenicoClient\Configuration;
use IngenicoClient\Client;
use IngenicoClient\Order;

$configuration = new Configuration('pspid', 'api_username', 'password', 'passphrase', 'sha512');
$order = new Order();
$order->setAmount(100);
$order->setCurrency('EUR');
$order->setOrderid('Order1');
$alias = new Alias('customer1');
$client = new Client($configuration);
$transaction = $client->createDirectLinkPayment($order, $alias);
```

# Payment status #

```php
use IngenicoClient\Configuration;
use IngenicoClient\Client;

$configuration = new Configuration('pspid', 'api_username', 'password', 'passphrase', 'sha512');
$client = new Client($configuration);
$payment_status = $client->getPaymentStatus('3041842086');
```

# Hosted Checkout #

```php
use IngenicoClient\Configuration;
use IngenicoClient\Client;
use IngenicoClient\Order;

$configuration = new Configuration('pspid', 'api_username', 'password', 'passphrase', 'sha512');
$client = new Client($configuration);
$order = new Order();
$order->setAmount(100);
$order->setCurrency('EUR');
$order->setOrderid('Order1');
$html = $client->initiateRedirectPayment($order);
```

# Add logger #

you can pass any logger implemented PSR LoggerInterface to log request/response data.

```php
use IngenicoClient\Configuration;
use IngenicoClient\IngenicoCoreLibrary;

$configuration = new Configuration('pspid', 'api_username', 'password', 'passphrase', 'sha512');
$client = new IngenicoCoreLibrary($configuration);
$client->selLogger($yourLogger);
```

to build your monolog logger use LoggerBuilder:
 
```php
use IngenicoClient\LoggerBuilder;

$yourLogger = (new LoggerBuilder())
  ->createLogger('log', '/tmp/test.log', Logger::DEBUG)
  ->getLogger(); 

```
 
to build your Gelf logger use LoggerBuilder:
 
```php
use IngenicoClient\LoggerBuilder;

$yourLogger = (new LoggerBuilder())
  ->createGelfLogger('log', 'logs.ing.limegrow.com', 12201, Logger::DEBUG)
  ->getLogger(); 

```
  