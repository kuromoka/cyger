<?php

namespace Cyptalt\Exchange;

use GuzzleHttp\Client;
use Noodlehaus\Config;
use Cyptalt\Exchange\BaseExchange;
use Cyptalt\Exchange\CoincheckExchange;

/**
 * CoincheckExchangeTest Class
 */
class CoincheckExchangeTest extends \PHPUnit_Framework_TestCase
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

    public function testGetValidPairsWithEmptyMarketResults()
    {
        $expected = ['btc_jpy'];

        $client = new Client(['http_errors' => false]);
        $exchange = new CoincheckExchange($this->conf['Coincheck'], $client);
        $marketResults = [];
        $actual = $exchange->getValidPairs($marketResults);
        
        $this->assertEquals($expected, $actual);
    }

    public function testGetUrl()
    {
        $expected = [
            'btc_jpy' => $this->conf['Coincheck']['baseUrl'] . $this->conf['Coincheck']['tickerPath'],
        ];

        $client = new Client(['http_errors' => false]);
        $exchange = new CoincheckExchange($this->conf['Coincheck'], $client);
        $pairs = [
            'btc_jpy' => 'btc_jpy',
        ];
        $jsonKey = BaseExchange::LAST_KEY;
        $actual = $exchange->getUrl($pairs, $jsonKey);

        $this->assertEquals($expected, $actual);
    }

    public function testParseResultWithValidJsonKey()
    {
        $expected = [
            'btc_jpy' => '1000000',
        ];

        $client = new Client(['http_errors' => false]);
        $exchange = new CoincheckExchange($this->conf['Coincheck'], $client);
        $pairs = [
            'btc_jpy' => [
                $this->conf['Coincheck']['lastKey'] => 1000000,
            ],
        ];
        $actual = $exchange->parseResult($pairs, 'lastKey');

        $this->assertEquals($expected, $actual);
    }

    public function testParseResultWithInvalidJsonKey()
    {
        $expected = [
            'btc_jpy' => null,
        ];

        $client = new Client(['http_errors' => false]);
        $exchange = new CoincheckExchange($this->conf['Coincheck'], $client);
        $pairs = [
            'btc_jpy' => [
                'lastPrice' => 1000000,
            ],
        ];
        $actual = $exchange->parseResult($pairs, 'lastKey');

        $this->assertEquals($expected, $actual);
    }
}
