<?php

namespace Cyptalt;

use Noodlehaus\Config;
use Pimple\Container;

/**
 * Client Class
 */
class Client
{
    const LAST_KEY = 'lastKey';

    /** @var array $conf config.json file content. */
    private $conf;

    /** @var array $container Pimple config. */
    private $container;

    /** @var ExchangeInterface[] $exchanges Exchange config. */  
    private $exchanges = [];

    /** @var string[] $pairs Pair config. */  
    private $pairs = [];

    /**
     * Read config.json and set Pimple config.
     */
    public function __construct()
    {
        $conffile = __DIR__ . '/config.json';
        $conf = new Config($conffile);
        $this->conf = $conf->all();
        $container = new Container();

        $containerKeys = array_keys($this->conf);
        foreach ($containerKeys as $containerKey) {
            $container['exchangeConf'] = $this->conf[$containerKey];                               
            $container['exchangeClass'] = 'Cyptalt\\Exchange\\' . $this->conf[$containerKey]["exchangeClass"];
            $container[$containerKey] = function ($c) {
                return new $c['exchangeClass']($c['exchangeConf']);
            };
        }
        $this->container = $container;
    }

    /**
     * Set Exchange config.
     * 
     * @param string $exchange
     * @return Client
     */
    public function setExchange($exchange)
    {
        $containerKeys = array_keys($this->conf);
        foreach ($containerKeys as $containerKey) {
            if ($exchange === $containerKey) {
                $this->exchanges[$containerKey] = $this->container[$containerKey];
            } else if (strtolower($exchange) === strtolower($containerKey)) {
                $this->exchanges[$exchange] = $this->container[$containerKey];                
            }
        }

        return $this;
    }

    /**
     * Set Pair config.
     * 
     * @param string $pair
     * @return Client
     */
    public function setPair($pair)
    {
        $this->pairs[$pair] = $pair;
        return $this;        
    }

    /**
     * Get Last Price.
     * 
     * @return array
     */
    public function getLastPrice()
    {
        $results = $this->getValue(self::LAST_KEY);
        return $results;
    }

     /**
     * Get Value. $jsonKey is constant value.
     * 
     * @param  array $jsonKey
     * @return array
     */
    private function getValue($jsonKey)
    {
        $results = [];

        if (count($this->exchanges) === 0) {
            $containerKeys = array_keys($this->conf);
            foreach ($containerKeys as $containerKey) {
                $this->exchanges[$containerKey] = $this->container[$containerKey];
            }
        }

        foreach ($this->exchanges as $key => $exchange) {
            $pairs = $exchange->normalizePairs($this->pairs);
            $pairs = $exchange->getUrl($pairs);
            $pairs = $exchange->sendRequest($pairs);
            $results[$key] = $exchange->parseResult($pairs, $jsonKey);
        }

        return $results;
    }
}