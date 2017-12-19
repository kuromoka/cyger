# cyptalt
[![Build Status](https://travis-ci.org/kuromoka/cyptalt.svg?branch=master)](https://travis-ci.org/kuromoka/cyptalt)
[![Coverage Status](https://coveralls.io/repos/github/kuromoka/cyptalt/badge.svg?branch=)](https://coveralls.io/github/kuromoka/cyptalt?branch=)

Cyptalt is a PHP library to get cryptocurrency price from various exchange APIs.
- It is possible to resolve different specifications for each api as soon as possible.
- It can get last price, bid price, ask price and volume.
- [Supporting exchanges](#supporting_exchanges)

## Install

```
$ composer require kuromoka/cyptalt
```

## Requirement

- PHP >= 5.5
- [Composer](https://getcomposer.org/)

## Usage

The first example code below shows getting last price of BTC_ETH from Poloniex.

```PHP
require 'vendor/autoload.php';

use Cyptalt\Client;

$client = new Client();
$result = $client->setExchange('Poloniex')->setPair('BTC_ETH')->getLastPrice();
echo $result['Poloniex']['BTC_ETH'];    // 0.04549105
```

The secound example code below shows getting last price of BTC_ETH from all [supporting exchanges](#supporting_exchanges). It is possible not to set exchange, but it is necessary to set pairs.  
if exchanges don't support pairs, returning NULL.

```PHP
$client = new Client();
$result = $client->setPair('BTC_ETH')->getLastPrice();
echo $result['Poloniex']['BTC_ETH'];     // 0.04549105
echo $result['Bittrex']['BTC_ETH'];      // 0.04577
echo $result['Coincheck']['BTC_ETH'];    // NULL (Coincheck doesn't support BTC_ETH pair.)
```

Also, you can use various style of exchange name and pair name. You don't have to think each api specifications basically.  
The example below shows OK and NG name.

```PHP
$client->setExchange('poloniex')    // OK
$client->setExchange('POLONIEX')    // OK
$client->setExchange('POLONIE')     // NG

$client->setPair('BTC_ETH');        // OK
$client->setPair('btc-eth');        // OK
$client->setPair('ETH/BTC');        // OK
$client->setPair('BTC:ETH');        // NG
```

Key name of results array is setting from your style.
```
$client = new Client();
$result = $client->setExchange('POLONIEX')->setPair('ETH/BTC')->getLastPrice();
echo $result['POLONIEX']['ETH/BTC'];    // 0.04549105
```

When you want to get other than last price, please replace getLastPrice() to methods below.

- getBidPrice()
- getAskPrice()
- getVolume()

## <a name ="supporting_exchanges"></a>Supporting exchanges

I am going to add more exchanges in the future.
- [bitFlyer](https://bitflyer.jp/)
- [Bittrex](https://bittrex.com/)
- [Coincheck](https://coincheck.com/)
- [Poloniex](https://poloniex.com/)
- [Zaif](https://zaif.jp/)

## Author

- Twitter  
  - [@kuromoka16](https://twitter.com/kuromoka16)
