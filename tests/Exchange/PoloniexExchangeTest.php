<?php

namespace Cyptalt\Exchange;

use GuzzleHttp\Client;
use Noodlehaus\Config;
use Cyptalt\Exchange\BaseExchange;
use Cyptalt\Exchange\PoloniexExchange;

/**
 * PoloniexExchangeTest Class
 */
class PoloniexExchangeTest extends \PHPUnit_Framework_TestCase
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
            'BTC_ETH',
            'BTC_BCH',
            'BTC_XRP',
        ];

        $client = new Client(['http_errors' => false]);
        $exchange = new PoloniexExchange($this->conf['Poloniex'], $client);
        $marketResults = [
            'BTC_ETH' => [],
            'BTC_BCH' => [],
            'BTC_XRP' => [],
        ];
        $actual = $exchange->getValidPairs($marketResults);
        
        $this->assertEquals($expected, $actual);
    }

    public function testGetValidPairsWithEmptyMarketResults()
    {
        $expected = [];

        $client = new Client(['http_errors' => false]);
        $exchange = new PoloniexExchange($this->conf['Poloniex'], $client);
        $marketResults = [];
        $actual = $exchange->getValidPairs($marketResults);
        
        $this->assertEquals($expected, $actual);
    }

    public function testParseResultWithValidJsonKey()
    {
        $expected = [
            'BTC_ETH' => '0.05',
            'BTC_BCH' => '0.20',
            'BTC_XRP' => '0.00002',
        ];

        $client = new Client(['http_errors' => false]);
        $exchange = new PoloniexExchange($this->conf['Poloniex'], $client);
        $pairs = [
            'BTC_ETH' => 'BTC_ETH',
            'BTC_BCH' => 'BTC_BCH',
            'BTC_XRP' => 'BTC_XRP',
        ];
        $marketResults = [
            'BTC_ETH' => [
                $this->conf['Poloniex']['lastKey'] => 0.05,
            ],
            'BTC_BCH' => [
                $this->conf['Poloniex']['lastKey'] => 0.20,
            ],
            'BTC_XRP' => [
                $this->conf['Poloniex']['lastKey'] => 0.00002,
            ],
        ];

        $actual = $exchange->parseResult($pairs, BaseExchange::LAST_KEY, $marketResults);

        $this->assertEquals($expected, $actual);
    }

    public function testParseResultWithInvalidJsonKey()
    {
        $expected = [
            'BTC_ETH' => null,
            'BTC_BCH' => null,
            'BTC_XRP' => null,
        ];

        $client = new Client(['http_errors' => false]);
        $exchange = new PoloniexExchange($this->conf['Poloniex'], $client);
        $pairs = [
            'BTC_ETH' => 'BTC_ETH',
            'BTC_BCH' => 'BTC_BCH',
            'BTC_XRP' => 'BTC_XRP',
        ];
        $marketResults = [
            'BTC_ETH' => [
                'lastPrice' => 0.05,
            ],
            'BTC_BCH' => [
                'lastPrice' => 0.20,
            ],
            'BTC_XRP' => [
                'lastPrice' => 0.00002,
            ],
        ];
        $actual = $exchange->parseResult($pairs, BaseExchange::LAST_KEY, $marketResults);

        $this->assertEquals($expected, $actual);
    }
}
