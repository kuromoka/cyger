<?php

namespace Cyger\Exchange;

use GuzzleHttp\Client;
use Noodlehaus\Config;
use Cyger\Exchange\BaseExchange;
use Cyger\Exchange\ZaifExchange;

/**
 * ZaifExchangeTest Class
 */
class ZaifExchangeTest extends \PHPUnit_Framework_TestCase
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
            'btc_jpy',
            'eth_btc',
            'mona_btc',
        ];

        $client = new Client(['http_errors' => false]);
        $exchange = new ZaifExchange($this->conf['Zaif'], $client);
        $marketResults = [
            [
                'currency_pair' => 'btc_jpy',
            ],
            [
                'currency_pair' => 'eth_btc',
            ],
            [
                'currency_pair' => 'mona_btc',
            ],
        ];
        $actual = $exchange->getValidPairs($marketResults);
        
        $this->assertEquals($expected, $actual);
    }

    public function testGetValidPairsWithEmptyMarketResults()
    {
        $expected = [];

        $client = new Client(['http_errors' => false]);
        $exchange = new ZaifExchange($this->conf['Zaif'], $client);
        $marketResults = [];
        $actual = $exchange->getValidPairs($marketResults);
        
        $this->assertEquals($expected, $actual);
    }

    public function testGetUrl()
    {
        $expected = [
            'BTC_JPY' => $this->conf['Zaif']['baseUrl'] . $this->conf['Zaif']['tickerPath'] . 'btc_jpy',
        ];

        $client = new Client(['http_errors' => false]);
        $exchange = new ZaifExchange($this->conf['Zaif'], $client);
        $pairs = [
            'BTC_JPY' => 'btc_jpy',
        ];
        $jsonKey = BaseExchange::LAST_KEY;
        $actual = $exchange->getUrl($pairs, $jsonKey);

        $this->assertEquals($expected, $actual);
    }

    public function testParseResultWithValidJsonKey()
    {
        $expected = [
            'btc_jpy' => '1000000',
            'eth_btc' => '0.05',
            'mona_btc' => '0.0001',
        ];

        $client = new Client(['http_errors' => false]);
        $exchange = new ZaifExchange($this->conf['Zaif'], $client);
        $pairs = [
            'btc_jpy' => [
                $this->conf['Zaif']['lastKey'] => 1000000,
            ],
            'eth_btc' => [
                $this->conf['Zaif']['lastKey'] => 0.05,
            ],
            'mona_btc' => [
                $this->conf['Zaif']['lastKey'] => 0.0001,
            ],
        ];
        $actual = $exchange->parseResult($pairs, BaseExchange::LAST_KEY);

        $this->assertEquals($expected, $actual);
    }

    public function testParseResultWithInvalidJsonKey()
    {
        $expected = [
            'btc_jpy' => null,
            'eth_btc' => null,
            'mona_btc' => null,
        ];

        $client = new Client(['http_errors' => false]);
        $exchange = new ZaifExchange($this->conf['Zaif'], $client);
        $pairs = [
            'btc_jpy' => [
                'lastPrice' => 1000000,
            ],
            'eth_btc' => [
                'lastPrice' => 0.05,
            ],
            'mona_btc' => [
                'lastPrice' => 0.0001,
            ],
        ];
        $actual = $exchange->parseResult($pairs, BaseExchange::LAST_KEY);

        $this->assertEquals($expected, $actual);
    }
}
