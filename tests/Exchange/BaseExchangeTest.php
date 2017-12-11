<?php

namespace Cyptalt\Exchange;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;
use Noodlehaus\Config;
use Cyptalt\Exchange\BaseExchange;
use Cyptalt\Exception\CouldNotConnectException;
use Cyptalt\Exception\InvalidValueException;

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

    public function testFetchMarketDataWithConnectableUrl()
    {
        $expected = [
            [
                'pair' => 'JPY_BTC',
            ],
            [
                'pair' => 'BTC_ETH',
            ],
            [
                'pair' => 'BTC_BCH',
            ],
        ];

        $r1 = new Response(200, [], '[{ "pair": "JPY_BTC" }, { "pair": "BTC_ETH" }, { "pair": "BTC_BCH" }]');
        $mock = new MockHandler([$r1]);
        $handler = HandlerStack::create($mock);      
        $client = new Client(['handler' => $handler, 'http_errors' => false]);
        $exchangeMock = $this->getMockForAbstractClass(
            BaseExchange::class, 
            array($this->testConf['FooExchange'], $client)
        );
        $actual = $exchangeMock->fetchMarketData();
        $this->assertEquals($expected, $actual);        
    }

    public function testFetchMarketDataWithUnConnectableUrl()
    {
        // TODO
        // Until I can know how to create RequestException Mock, I will defer writing this test code.
        $this->assertEquals(true, true);  
    }
    
    public function testNormalizePairsWithPairsToRequireNormalization()
    {
        $expected = [
            'btc_jpy' => 'JPY_BTC',            
            'btc-eth' => 'BTC_ETH',
            'btc/bch' => 'BTC_BCH',
            'BTC-XRP' => 'BTC_XRP',                                         
            'BTC/BTG' => 'BTC_BTG',
        ];

        $client = new Client(['http_errors' => false]);
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
        $validPairs = [
            'JPY_BTC',   
            'BTC_ETH',
            'BTC_BCH',
            'BTC_XRP',
            'BTC_BTG',
        ];
        $actual = $exchangeMock->normalizePairs($pairs, $validPairs);

        $this->assertEquals($expected, $actual);
    }

    public function testNormalizePairsWithPairsNotToRequireNormalization()
    {
        $expected = [
            'BTC_JPY' => 'BTC_JPY',
        ];

        $client = new Client(['http_errors' => false]);
        $exchangeMock = $this->getMockForAbstractClass(
            BaseExchange::class, 
            array($this->testConf['FooExchange'], $client)
        );
        $pairs = [
            'BTC_JPY' => 'BTC_JPY',
        ];
        $validPairs = [
            'BTC_JPY' => 'BTC_JPY',
        ];
        $actual = $exchangeMock->normalizePairs($pairs, $validPairs);

        $this->assertEquals($expected, $actual);
    }

    public function testNormalizePairsWithInvalidPairs()
    {
        $this->setExpectedException(InvalidValueException::class);    

        $client = new Client(['http_errors' => false]);
        $exchangeMock = $this->getMockForAbstractClass(
            BaseExchange::class, 
            array($this->testConf['FooExchange'], $client)
        );
        $pairs = [
            'BTC_JPYY' => 'BTC_JPYY',
        ];
        $validPairs = [
            'BTC_JPY' => 'BTC_JPY',
        ];
        $actual = $exchangeMock->normalizePairs($pairs, $validPairs);
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
        $client = new Client(['handler' => $handler, 'http_errors' => false]);
        $exchangeMock = $this->getMockForAbstractClass(
            BaseExchange::class, 
            array($this->testConf['FooExchange'], $client)
        );
        $pairs = [
            'BTC_JPY' => $this->testConf['FooExchange']['baseUrl'] . $this->testConf['FooExchange']['tickerPath'] . 'BTC_JPY',
            'BTC_ETH' => $this->testConf['FooExchange']['baseUrl'] . $this->testConf['FooExchange']['tickerPath'] . 'BTC_ETH',
            'BTC_BCH' => $this->testConf['FooExchange']['baseUrl'] . $this->testConf['FooExchange']['tickerPath'] . 'BTC_BCH',            
        ];
        $actual = $exchangeMock->sendRequest($pairs);

        $this->assertEquals($expected, $actual);        
    }

    public function testSendRequestWithUnConnectableUrl()
    {
        $this->setExpectedException(CouldNotConnectException::class);
       
        $client = new Client(['http_errors' => false]);
        $exchangeMock = $this->getMockForAbstractClass(
            BaseExchange::class, 
            array($this->testConf['FooExchange'], $client)
        );
        $pairs = [
            'BTC_JPY' => null,
        ];
        $actual = $exchangeMock->sendRequest($pairs);
    }
}
