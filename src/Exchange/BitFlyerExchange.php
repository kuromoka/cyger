<?php

namespace Cyptalt\Exchange;

/**
 * BitFlyerExchange Class
 */
class BitFlyerExchange extends BaseExchange
{
    /**
     * {@inheritDoc}
     */
    public function getUrl($pairs)
    {
        $reversedPairs = [
            'BTC_ETH' => 'ETH_BTC',
            'BTC_BCH' => 'BCH_BTC',            
        ];
        foreach ($pairs as $key => $pair) {
            if (array_key_exists($pair, $reversedPairs)) {
                $pairs[$key] = $this->conf['baseUrl'] . $this->conf['tickerPath'] . $reversedPairs[$pair];                                 
            } else {
                $pairs[$key] = $this->conf['baseUrl'] . $this->conf['tickerPath'] . $pair;                 
            }
        }

        return $pairs;        
    }

    /**
     * {@inheritDoc}
     */
    public function parseResult($pairs, $jsonKey)
    {
        foreach ($pairs as $key => $pair) {
            if (isset($pair[$this->conf[$jsonKey]])) {
                $pairs[$key] = strval($pair[$this->conf[$jsonKey]]);
            } else {
                $pairs[$key] = null;
            }
        }
        
        return $pairs;        
    }
}