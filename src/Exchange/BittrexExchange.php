<?php

namespace Cyger\Exchange;

/**
 * BittrexExchange Class
 */
class BittrexExchange extends BaseExchange
{
    /**
     * {@inheritDoc}
     */
    public function getValidPairs($marketResults)
    {
        $validPairs = [];
        if (isset($marketResults['result'])) {
            foreach ($marketResults['result'] as $marketResult) {
                if (isset($marketResult['MarketName'])) {
                    $validPairs[] = $marketResult['MarketName'];
                }
            }
        }

        return $validPairs;
    }

    /**
     * {@inheritDoc}
     */
    public function getUrl($pairs, $jsonKey)
    {
        foreach ($pairs as $key => $pair) {
            if (!is_null($pair)) {
                if ($jsonKey === parent::VOLUME_KEY) {
                    $pairs[$key] = $this->conf['baseUrl'] . $this->conf['volumePath'] . $pair;
                } else {
                    $pairs[$key] = $this->conf['baseUrl'] . $this->conf['tickerPath'] . $pair;
                }
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
            if (isset($pair['result']) && isset($pair['result'][$this->conf[$jsonKey]])) {
                $pairs[$key] = strval($pair['result'][$this->conf[$jsonKey]]);
            } else {
                $pairs[$key] = null;
            }
        }
        
        return $pairs;
    }
}
