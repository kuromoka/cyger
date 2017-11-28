<?php

namespace Cyptalt\Exchange;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Noodlehaus\Config;
use Cyptalt\Exchange\BaseExchange;
use Cyptalt\Exception\CouldNotConnectException;

/**
 * BaseExchangeTest Class
 */
class BaseExchangeTest extends \PHPUnit_Framework_TestCase
{
    /** @var array $testConf testconfig.json file content. */
    private $testConf;

    protected function setUp()
    {
        $testConfFile = __DIR__ . '/../testconfig.json';
        $testConf = new Config($testConfFile);
        $this->testConf = $testConf->all();
    }

    public function testNormalizePairsWithPairsToRequireNormalization()
    {
        $expected = [
            'btc_jpy' => 'BTC_JPY',            
            'btc-eth' => 'BTC_ETH',
            'btc/bch' => 'BTC_BCH',
            'BTC-XRP' => 'BTC_XRP',                                         
            'BTC/BTG' => 'BTC_BTG',
        ];

        $client = new Client();
        $exchangeMock = $this->getMockForAbstractClass(
            BaseExchange::class, 
            array($this->testConf['FooExchange'], $client)
        );
        $pairs = [
            'btc_jpy' => 'btc_jpy',            
            'btc-eth' => 'btc-eth',
            'btc/bch' => 'btc/bch',
            'BTC-XRP' => 'BTC-XRP',
            'BTC/BTG' => 'BTC/BTG',
        ];
        $actual = $exchangeMock->normalizePairs($pairs);

        $this->assertEquals($expected, $actual);
    }

    public function testNormalizePairsWithPairsNotToRequireNormalization()
    {
        $expected = [
            'BTC_JPY' => 'BTC_JPY',
        ];

        $client = new Client();
        $exchangeMock = $this->getMockForAbstractClass(
            BaseExchange::class, 
            array($this->testConf['FooExchange'], $client)
        );
        $pairs = [
            'BTC_JPY' => 'BTC_JPY',
        ];
        $actual = $exchangeMock->normalizePairs($pairs);

        $this->assertEquals($expected, $actual);
    }

    public function testSendRequestWithConnectableUrl()
    {
        $expected = [
            'BTC_JPY' => [
                'last' => 1000000,
            ],            
            'BTC_ETH' => [
                'last' => 0.05,
            ],
            'BTC_BCH' => [
                'last' => 0.20,
            ],
        ];

        $r1 = new Response(200, [], '{ "last": 1000000 }');
        $r2 = new Response(200, [], '{ "last": 0.05 }');
        $r3 = new Response(200, [], '{ "last": 0.20 }');
        $mock = new MockHandler([$r1, $r2, $r3]);
        $handler = HandlerStack::create($mock);      
        $client = new Client(['handler' => $handler]);
        $exchangeMock = $this->getMockForAbstractClass(
            BaseExchange::class, 
            array($this->testConf['FooExchange'], $client)
        );
        $pairs = [
            'BTC_JPY' => $this->testConf['FooExchange']['baseUrl'] . $this->testConf['FooExchange']['requestPath'] . 'BTC_JPY',
            'BTC_ETH' => $this->testConf['FooExchange']['baseUrl'] . $this->testConf['FooExchange']['requestPath'] . 'BTC_ETH',
            'BTC_BCH' => $this->testConf['FooExchange']['baseUrl'] . $this->testConf['FooExchange']['requestPath'] . 'BTC_BCH',            
        ];
        $actual = $exchangeMock->sendRequest($pairs);

        $this->assertEquals($expected, $actual);        
    }

    public function testSendRequestWithUnConnectableUrl()
    {
        $this->setExpectedException(CouldNotConnectException::class);

        $r1 = new Response(404);
        $mock = new MockHandler([$r1]);
        $handler = HandlerStack::create($mock);        
        $client = new Client(['handler' => $handler]);
        $exchangeMock = $this->getMockForAbstractClass(
            BaseExchange::class, 
            array($this->testConf['FooExchange'], $client)
        );
        $pairs = [
            'BTC_JPY' => $this->testConf['FooExchange']['baseUrl'] . $this->testConf['FooExchange']['requestPath'] . 'BTC_JPY',
        ];
        $actual = $exchangeMock->sendRequest($pairs);
    }
}
