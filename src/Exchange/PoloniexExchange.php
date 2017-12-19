<?php

namespace Cyptalt\Exchange;

/**
 * PoloniexExchange Class
 */
class PoloniexExchange extends BaseExchange
{
    /**
     * {@inheritDoc}
     */
    public function getValidPairs($marketResults)
    {
        $validPairs = [];
        foreach ($marketResults as $key => $marketResult) {
            if (!empty($key)) {
                $validPairs[] = $key;
            }
        }

        return $validPairs;
    }

    /**
     * {@inheritDoc}
     */
    public function getUrl($pairs, $jsonKey)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function parseResult($pairs, $jsonKey, $marketResults = null)
    {
        foreach ($pairs as $key => $pair) {
            if (isset($marketResults[$pair]) && isset($marketResults[$pair][$this->conf[$jsonKey]])) {
                $pairs[$key] = strval($marketResults[$pair][$this->conf[$jsonKey]]);
            } else {
                $pairs[$key] = null;
            }
        }
        
        return $pairs;
    }
}
