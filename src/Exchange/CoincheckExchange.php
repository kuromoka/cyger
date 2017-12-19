<?php

namespace Cyptalt\Exchange;

/**
 * CoincheckExchange Class
 */
class CoincheckExchange extends BaseExchange
{
    /**
     * {@inheritDoc}
     */
    public function getValidPairs($marketResults)
    {
        $validPairs = [];
        $validPairs[] = 'btc_jpy';

        return $validPairs;
    }

    /**
     * {@inheritDoc}
     */
    public function getUrl($pairs, $jsonKey)
    {
        foreach ($pairs as $key => $pair) {
            if (!is_null($pair)) {
                $pairs[$key] = $this->conf['baseUrl'] . $this->conf['tickerPath'];
            }
        }

        return $pairs;
    }

    /**
     * {@inheritDoc}
     */
    public function parseResult($pairs, $jsonKey, $marketResults = null)
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
