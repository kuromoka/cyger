# cyptalt
[![Build Status](https://travis-ci.org/kuromoka/cyptalt.svg?branch=master)](https://travis-ci.org/kuromoka/cyptalt)
[![Coverage Status](https://coveralls.io/repos/github/kuromoka/cyptalt/badge.svg?branch=)](https://coveralls.io/github/kuromoka/cyptalt?branch=)

Cyptalt is a PHP library to get cryptocurrency price from various exchange APIs.
- It is possible to resolve different specifications for each api as soon as possible.
- It can get last price, bid price, ask price and volume.

## Usage

The first example code below shows getting last price of BTC_ETH from Poloniex.

```PHP
require 'vendor/autoload.php';

use Cyptalt\Client;

$client = new Client();
$result = $client->setExchange('Poloniex')->setPair('BTC_ETH')->getLastPrice();
echo $result['Poloniex']['BTC_ETH'];    // 0.04549105
```

The secound example code below shows getting last price of BTC_ETH from all supporting exchange.  
if exchanges don't support pairs, returning NULL.

```PHP
require 'vendor/autoload.php';

use Cyptalt\Client;

$client = new Client();
$result = $client->setPair('BTC_ETH')->getLastPrice();
echo $result['Poloniex']['BTC_ETH'];     // 0.04549105
echo $result['Bittrex']['BTC_ETH'];      // 0.04577
echo $result['Coincheck']['BTC_ETH'];    // NULL (Coincheck doesn't support BTC_ETH pair.)
```

When you want to get other than last price, please replace getLastPrice() to methods below.

- getBidPrice()
- getAskPrice()
- getVolume()

## Install

```
$ composer require kuromoka/cyptalt
```

## Requirement

- PHP >= 5.5
- [Composer](https://getcomposer.org/)

## Author

- Twitter  
  - [@kuromoka16](https://twitter.com/kuromoka16)
