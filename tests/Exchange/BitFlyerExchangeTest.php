<?php

namespace Cyger\Exchange;

use GuzzleHttp\Client;
use Noodlehaus\Config;
use Cyger\Exchange\BaseExchange;
use Cyger\Exchange\BitFlyerExchange;

/**
 * BitFlyerExchangeTest Class
 */
class BitFlyerExchangeTest extends \PHPUnit_Framework_TestCase
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
            'BTC_JPY',
            'ETH_BTC',
            'BCH_BTC',
        ];

        $client = new Client(['http_errors' => false]);
        $exchange = new BitFlyerExchange($this->conf['bitFlyer'], $client);
        $marketResults = [
            [
                'product_code' => 'BTC_JPY',
            ],
            [
                'product_code' => 'ETH_BTC',
            ],
            [
                'product_code' => 'BCH_BTC',
            ],
        ];
        $actual = $exchange->getValidPairs($marketResults);
        
        $this->assertEquals($expected, $actual);
    }

    public function testGetValidPairsWithEmptyMarketResults()
    {
        $expected = [];

        $client = new Client(['http_errors' => false]);
        $exchange = new BitFlyerExchange($this->conf['bitFlyer'], $client);
        $marketResults = [];
        $actual = $exchange->getValidPairs($marketResults);
        
        $this->assertEquals($expected, $actual);
    }

    public function testGetUrl()
    {
        $expected = [
            'BTC_ETH' => $this->conf['bitFlyer']['baseUrl'] . $this->conf['bitFlyer']['tickerPath'] . 'ETH_BTC',
        ];

        $client = new Client(['http_errors' => false]);
        $exchange = new BitFlyerExchange($this->conf['bitFlyer'], $client);
        $pairs = [
            'BTC_ETH' => 'ETH_BTC',
        ];
        $jsonKey = BaseExchange::LAST_KEY;
        $actual = $exchange->getUrl($pairs, $jsonKey);

        $this->assertEquals($expected, $actual);
    }

    public function testParseResultWithValidJsonKey()
    {
        $expected = [
            'BTC_JPY' => '1000000',
            'ETH_BTC' => '0.05',
            'BCH_BTC' => '0.20',
        ];

        $client = new Client(['http_errors' => false]);
        $exchange = new BitFlyerExchange($this->conf['bitFlyer'], $client);
        $pairs = [
            'BTC_JPY' => [
                $this->conf['bitFlyer']['lastKey'] => 1000000,
            ],
            'ETH_BTC' => [
                $this->conf['bitFlyer']['lastKey'] => 0.05,
            ],
            'BCH_BTC' => [
                $this->conf['bitFlyer']['lastKey'] => 0.20,
            ],
        ];
        $actual = $exchange->parseResult($pairs, BaseExchange::LAST_KEY);

        $this->assertEquals($expected, $actual);
    }

    public function testParseResultWithInvalidJsonKey()
    {
        $expected = [
            'BTC_JPY' => null,
            'ETH_BTC' => null,
            'BCH_BTC' => null,
        ];

        $client = new Client(['http_errors' => false]);
        $exchange = new BitFlyerExchange($this->conf['bitFlyer'], $client);
        $pairs = [
            'BTC_JPY' => [
                'last' => 1000000,
            ],
            'ETH_BTC' => [
                'last' => 0.05,
            ],
            'BCH_BTC' => [
                'last' => 0.20,
            ],
        ];
        $actual = $exchange->parseResult($pairs, BaseExchange::LAST_KEY);

        $this->assertEquals($expected, $actual);
    }
}
