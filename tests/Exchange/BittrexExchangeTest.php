<?php

namespace Cyger\Exchange;

use GuzzleHttp\Client;
use Noodlehaus\Config;
use Cyger\Exchange\BaseExchange;
use Cyger\Exchange\BittrexExchange;

/**
 * BittrexExchangeTest Class
 */
class BittrexExchangeTest extends \PHPUnit_Framework_TestCase
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
            'BTC-ETH',
            'BTC-BCC',
            'BTC-XRP',
        ];

        $client = new Client(['http_errors' => false]);
        $exchange = new BittrexExchange($this->conf['Bittrex'], $client);
        $marketResults = [
            'result' => [
                [
                    'MarketName' => 'BTC-ETH',
                ],
                [
                    'MarketName' => 'BTC-BCC',
                ],
                [
                    'MarketName' => 'BTC-XRP',
                ],
            ],
        ];
        $actual = $exchange->getValidPairs($marketResults);

        $this->assertEquals($expected, $actual);
    }

    public function testGetValidPairsWithEmptyMarketResults()
    {
        $expected = [];

        $client = new Client(['http_errors' => false]);
        $exchange = new BittrexExchange($this->conf['Bittrex'], $client);
        $marketResults = [];
        $actual = $exchange->getValidPairs($marketResults);
        
        $this->assertEquals($expected, $actual);
    }

    public function testGetUrlWithLastJsonKey()
    {
        $expected = [
            'BTC-ETH' => $this->conf['Bittrex']['baseUrl'] . $this->conf['Bittrex']['tickerPath'] . 'BTC-ETH',
        ];

        $client = new Client(['http_errors' => false]);
        $exchange = new BittrexExchange($this->conf['Bittrex'], $client);
        $pairs = [
            'BTC-ETH' => 'BTC-ETH',
        ];
        $jsonKey = BaseExchange::LAST_KEY;
        $actual = $exchange->getUrl($pairs, $jsonKey);

        $this->assertEquals($expected, $actual);
    }

    public function testGetUrlWithVolumeJsonKey()
    {
        $expected = [
            'BTC-ETH' => $this->conf['Bittrex']['baseUrl'] . $this->conf['Bittrex']['volumePath'] . 'BTC-ETH',
        ];

        $client = new Client(['http_errors' => false]);
        $exchange = new BittrexExchange($this->conf['Bittrex'], $client);
        $pairs = [
            'BTC-ETH' => 'BTC-ETH',
        ];
        $jsonKey = BaseExchange::VOLUME_KEY;
        $actual = $exchange->getUrl($pairs, $jsonKey);

        $this->assertEquals($expected, $actual);
    }

    public function testParseResultWithValidJsonKey()
    {
        $expected = [
            'BTC-ETH' => '0.05',
            'BTC-BCC' => '0.20',
            'BTC-XRP' => '0.00002',
        ];

        $client = new Client(['http_errors' => false]);
        $exchange = new BittrexExchange($this->conf['Bittrex'], $client);
        $pairs = [
            'BTC-ETH' => [
                'result' => [
                    $this->conf['Bittrex']['lastKey'] => 0.05,
                ]
            ],
            'BTC-BCC' => [
                'result' => [
                    $this->conf['Bittrex']['lastKey'] => 0.20,
                ]
            ],
            'BTC-XRP' => [
                'result' => [
                    $this->conf['Bittrex']['lastKey'] => 0.00002,
                ]
            ],
        ];
        $actual = $exchange->parseResult($pairs, BaseExchange::LAST_KEY);

        $this->assertEquals($expected, $actual);
    }

    public function testParseResultWithInvalidJsonKey()
    {
        $expected = [
            'BTC-ETH' => null,
            'BTC-BCC' => null,
            'BTC-XRP' => null,
        ];

        $client = new Client(['http_errors' => false]);
        $exchange = new BittrexExchange($this->conf['Bittrex'], $client);
        $pairs = [
            'BTC-ETH' => [
                'result' => [
                    'LastPrice' => 0.05,
                ]
            ],
            'BTC-BCC' => [
                'result' => [
                    'LastPrice' => 0.20,
                ]
            ],
            'BTC-XRP' => [
                'result' => [
                    'LastPrice' => 0.00002,
                ]
            ],
        ];
        $actual = $exchange->parseResult($pairs, BaseExchange::LAST_KEY);

        $this->assertEquals($expected, $actual);
    }
}
