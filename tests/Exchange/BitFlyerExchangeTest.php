<?php

namespace Cyptalt\Exchange;

use GuzzleHttp\Client;
use Noodlehaus\Config;
use Cyptalt\Exchange\BitFlyerExchange;

/**
 * BitFlyerExchangeTest Class
 */
class BitFlyerExchangeTest extends \PHPUnit_Framework_TestCase
{
    /** @var array $conf config.json file content. */
    private $conf;
    /** @var array $testConf testconfig.json file content. */
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

    public function testGetUrlWithPairsToRequireReverse()
    {
        $expected = [
            'BTC_ETH' => $this->conf['bitFlyer']['baseUrl'] . $this->conf['bitFlyer']['requestPath'] . 'ETH_BTC',            
        ];

        $client = new Client();
        $exchange = new BitFlyerExchange($this->conf['bitFlyer'], $client);
        $pairs = [
            'BTC_ETH' => 'BTC_ETH',            
        ];
        $actual = $exchange->getUrl($pairs);

        $this->assertEquals($expected, $actual);
    }

    public function testGetUrlWithPairsNotToRequireReverse()
    {
        $expected = [
            'BTC_JPY' => $this->conf['bitFlyer']['baseUrl'] . $this->conf['bitFlyer']['requestPath'] . 'BTC_JPY',            
        ];

        $client = new Client();
        $exchange = new BitFlyerExchange($this->conf['bitFlyer'], $client);
        $pairs = [
            'BTC_JPY' => 'BTC_JPY',            
        ];
        $actual = $exchange->getUrl($pairs);

        $this->assertEquals($expected, $actual);
    }

    public function testParseResultWithValidJsonKey()
    {
        $expected = [
            'BTC_JPY' => '1000000',
            'ETH_BTC' => '0.05',
            'BCH_BTC' => '0.20',
        ];

        $client = new Client();
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
        $actual = $exchange->parseResult($pairs, 'lastKey');

        $this->assertEquals($expected, $actual);
    }

    public function testParseResultWithInvalidJsonKey()
    {
        $expected = [
            'BTC_JPY' => null,
            'ETH_BTC' => null,
            'BCH_BTC' => null,
        ];

        $client = new Client();
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
        $actual = $exchange->parseResult($pairs, 'lastKey');

        $this->assertEquals($expected, $actual);
    }
}
