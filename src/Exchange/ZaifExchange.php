<?php

namespace Cyptalt\Exchange;

/**
 * ZaifExchange Class
 */
class ZaifExchange extends BaseExchange
{
    /**
     * {@inheritDoc}
     */
    public function getValidPairs($marketResults)
    {
        $validPairs = [];
        foreach ($marketResults as $marketResult) {
            if (isset($marketResult['currency_pair'])) {
                $validPairs[] = $marketResult['currency_pair'];
            }
        }

        return $validPairs;
    }

    /**
     * {@inheritDoc}
     */
    public function getUrl($pairs)
    {
        foreach ($pairs as $key => $pair) {
            if (!is_null($pair)) {
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
