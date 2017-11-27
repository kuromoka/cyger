<?php

namespace Cyptalt;

use Noodlehaus\Config;
use Cyptalt\Client;
use Cyptalt\Exchange;
use Cyptalt\Exception\NotSetException;

/**
 * ClientTest Class
 */
class ClientTest extends \PHPUnit_Framework_TestCase
{
    /** @var array $conf config.json file content. */
    private $conf;
    /** @var array $testConf testconfig.json file content. */
    private $testConf;

    protected function setUp()
    {
        $confFile = __DIR__ . '/../src/config.json';
        $conf = new Config($confFile);
        $this->conf = $conf->all();

        $testConfFile = __DIR__ . '/testconfig.json';
        $testConf = new Config($testConfFile);
        $this->testConf = $testConf->all();
    }

    public function testSetExchangeWithIncorrectName()
    {
        $guzzleClient = new \GuzzleHttp\Client();
        $exchangeConf1 = $this->conf['bitFlyer'];
        $exchangeClass1 = 'Cyptalt\\Exchange\\' . $this->conf['bitFlyer']["exchangeClass"];      
        $expected = [
            'BitFlyer' => new $exchangeClass1($exchangeConf1, $guzzleClient),
        ];

        $client = new Client();        
        $client->setExchange('BitFlyer');
        $clientProps = $this->getClientProps($client);
        $actual = $clientProps['exchanges'];

        $this->assertEquals($expected, $actual);        
    }

    public function testSetExchangeWithCorrectName()
    {
        $guzzleClient = new \GuzzleHttp\Client();
        $exchangeConf1 = $this->conf['bitFlyer'];
        $exchangeClass1 = 'Cyptalt\\Exchange\\' . $this->conf['bitFlyer']["exchangeClass"];      
        $expected = [
            'bitFlyer' => new $exchangeClass1($exchangeConf1, $guzzleClient),
        ];

        $client = new Client();        
        $client->setExchange('bitFlyer');
        $clientProps = $this->getClientProps($client);
        $actual = $clientProps['exchanges'];

        $this->assertEquals($expected, $actual);        
    }

    public function testSetPair()
    {
        $expected = [
            'BTC_JPY' => 'BTC_JPY',
            'ETH_BTC' => 'ETH_BTC',
            'BCH_BTC' => 'BCH_BTC',
        ];

        $client = new Client();
        $client->setPair('BTC_JPY')->setPair('ETH_BTC')->setPair('BCH_BTC');
        $clientProps = $this->getClientProps($client);
        $actual = $clientProps['pairs'];

        $this->assertEquals($expected, $actual); 
    }

    public function testGetLastPriceWithoutSettingPairs()
    {
        $this->setExpectedException(NotSetException::class);

        $client = new Client();
        $results = $client->setExchange('bitFlyer')->getLastPrice();
    }

    /**
     * This test checks whether to only get last price, but don't (can't) check last price value.
     */
    public function testGetLastPriceWithRealRequestToAllExchanges()
    {
        $expected = [];        
        $containerKeys = array_keys($this->conf);
        foreach ($containerKeys as $containerKey) {
            $expected[$containerKey] = [
                'BTC_ETH' => 'string',
            ];
        }

        $client = new Client();
        $results = $client->setPair('BTC_ETH')->getLastPrice();
        if (!empty($results)) {
            foreach ($results as $key => $result) {
                if (gettype($result['BTC_ETH']) === 'string') {
                    $results[$key]['BTC_ETH'] = 'string';
                }
            }
            $actual = $results;
        } else {
            $actual = $results;
        }

        $this->assertEquals($expected, $actual); 
    }

    /**
     * Get Client Class Properties.
     * 
     * @param Client $client
     * @return array
     */
    private function getClientProps($client)
    {
        $clientProps = [];        
        $reflClient = new \ReflectionClass($client);
        $props = $reflClient->getProperties();
        foreach ($props as $prop) {
            $prop->setAccessible(true);
            $clientProps[$prop->getName()] = $prop->getValue($client);
        }

        return $clientProps;
    }
}
