<?php

namespace Cyger\Exchange;

use GuzzleHttp\Client;
use Noodlehaus\Config;
use Cyger\Exchange\BaseExchange;
use Cyger\Exchange\BinanceExchange;

/**
 * BinanceExchangeTest Class
 */
class BinanceExchangeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array $conf config.json file content.
     */
    private $conf;
    /**
     * @var array $testConf testconfig.json file content.
     */
    private $testConf;

    protected function setUp()
    {
        $confFile = __DIR__ . '/../../src/config.json';
        $conf = new Config($confFile);
        $this->conf = $conf->all();

        $testConfFile = __DIR__ . '/../testconfig.json';
        $testConf = new Config($testConfFile);
        $this->testConf = $testConf->all();
    }

    public function testGetValidPairsWithNoEmptyMarketResults()
    {
        $expected = [
            'ETHBTC',
            'LTCBTC',
            'BNBBTC',
        ];

        $client = new Client(['http_errors' => false]);
        $exchange = new BinanceExchange($this->conf['Binance'], $client);
        $marketResults = [
            [
                'symbol' => 'ETHBTC',
            ],
            [
                'symbol' => 'LTCBTC',
            ],
            [
                'symbol' => 'BNBBTC',
            ],
        ];
        $actual = $exchange->getValidPairs($marketResults);
        
        $this->assertEquals($expected, $actual);
    }

    public function testGetValidPairsWithEmptyMarketResults()
    {
        $expected = [];

        $client = new Client(['http_errors' => false]);
        $exchange = new BinanceExchange($this->conf['Binance'], $client);
        $marketResults = [];
        $actual = $exchange->getValidPairs($marketResults);
        
        $this->assertEquals($expected, $actual);
    }

    public function testGetUrl()
    {
        $expected = [
            'BTC_ETH' => $this->conf['Binance']['baseUrl'] . $this->conf['Binance']['tickerPath'] . 'ETHBTC',
        ];

        $client = new Client(['http_errors' => false]);
        $exchange = new BinanceExchange($this->conf['Binance'], $client);
        $pairs = [
            'BTC_ETH' => 'ETHBTC',
        ];
        $jsonKey = BaseExchange::LAST_KEY;
        $actual = $exchange->getUrl($pairs, $jsonKey);

        $this->assertEquals($expected, $actual);
    }

    public function testParseResultWithValidJsonKey()
    {
        $expected = [
            'ETH_BTC' => '0.05',
            'LTC_BTC' => '0.01',
            'BNB_BTC' => '0.0015',
        ];

        $client = new Client(['http_errors' => false]);
        $exchange = new BinanceExchange($this->conf['Binance'], $client);
        $pairs = [
            'ETH_BTC' => [
                $this->conf['Binance']['lastKey'] => 0.05,
            ],
            'LTC_BTC' => [
                $this->conf['Binance']['lastKey'] => 0.01,
            ],
            'BNB_BTC' => [
                $this->conf['Binance']['lastKey'] => 0.0015,
            ],
        ];
        $actual = $exchange->parseResult($pairs, BaseExchange::LAST_KEY);

        $this->assertEquals($expected, $actual);
    }

    public function testParseResultWithInvalidJsonKey()
    {
        $expected = [
            'ETH_BTC' => null,
            'LTC_BTC' => null,
            'BNB_BTC' => null,
        ];

        $client = new Client(['http_errors' => false]);
        $exchange = new BinanceExchange($this->conf['Binance'], $client);
        $pairs = [
            'ETH_BTC' => [
                'last' => 1000000,
            ],
            'LTC_BTC' => [
                'last' => 0.05,
            ],
            'BNB_BTC' => [
                'last' => 0.20,
            ],
        ];
        $actual = $exchange->parseResult($pairs, BaseExchange::LAST_KEY);

        $this->assertEquals($expected, $actual);
    }
}
