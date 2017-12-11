<?php

namespace Cyptalt\Exchange;

use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Cyptalt\Exception;
use Cyptalt\Exception\CouldNotConnectException;

/**
 * BaseExchange Class
 */
abstract class BaseExchange
{
    const UPPER_CASE = 'uppercase';
    const LOWER_CASE = 'lowercase';

    /** @var array $conf config.json file content of Child exchange. */
    protected $conf;

    /** @var array $client GuzzleHttp\Client Object. */
    protected $client;

    /**
     * Set config.json file content of Child exchange.
     * 
     * @param  array             $conf
     * @param  GuzzleHttp\Client $client
     */
    public function __construct($conf, $client)
    {
        $this->conf = $conf;
        $this->client = $client;
    }

    /**
     * Get valid pairs.
     * 
     * @param  array $pairs
     */
    abstract public function getValidPairs($marketResults);

    /**
     * Get API Url from pair config.
     * 
     * @param  array $pairs
     */
    abstract public function getUrl($pairs);
    
    /**
     * Get value from API result.
     * 
     * @param  array  $pairs
     * @param  string $jsonKey
     */
    abstract public function parseResult($pairs, $jsonKey);

    /**
     * Fetch available market data from API.
     * 
     * @return array
     */
    public function fetchMarketData()
    {
        $marketResults = [];
        $marketUrl = $this->conf['baseUrl'] . $this->conf['marketPath'];
        if (!empty($marketUrl)) {
            try {
                $response = $this->client->request('GET', $marketUrl);
                $marketResults = json_decode($response->getBody()->getContents(), true);
            } catch (Exception $e) {
                throw new CouldNotConnectException($e->getMessage());      
            }
        }

        return $marketResults;
    }

    /**
     * According to API, normalize pair config.
     * 
     * @param  array $pairs
     * @param  array $validPairs
     * @return array
     */
    public function normalizePairs($pairs, $validPairs)
    {
        $searchSymbolDelimiters = ['_', '-', '/'];
        foreach ($pairs as $key => $pair) {
            if ($this->conf['symbolLetter'] === self::UPPER_CASE) {
                $pairs[$key] = strtoupper($pair);
            } else if ($this->conf['symbolLetter'] === self::LOWER_CASE) {
                $pairs[$key] = strtolower($pair);                
            }

            if(strpos($pair, $this->conf['symbolDelimiter']) === false) {
                $pairs[$key] = str_replace($searchSymbolDelimiters, $this->conf['symbolDelimiter'], $pairs[$key]);
            }

            $pieces = explode($this->conf['symbolDelimiter'], $pairs[$key]);
            if (count($pieces) === 2) {
                if (in_array($pieces[1]. $this->conf['symbolDelimiter'] . $pieces[0], $validPairs)) {
                    $pairs[$key] = $pieces[1]. $this->conf['symbolDelimiter'] . $pieces[0];
                }
            }
        }

        return $pairs;
    }

    /**
     * Send request to API.
     * 
     * @param  array $pairs
     * @return array
     */
    public function sendRequest($pairs)
    {
        $requests = function ($pairs) {
            foreach ($pairs as $key => $pair) {
                yield $key => new Request('GET', $pair);
            }
        };
        $pool = new Pool($this->client, $requests($pairs), [
            'concurrency' => 5,
            'fulfilled' => function ($response, $index) use (&$pairs) {
                $pairs[$index] = json_decode($response->getBody()->getContents(), true);
            },
            'rejected' => function ($reason, $index) {
                throw new CouldNotConnectException($reason->getMessage());        
            },
        ]);
        $promise = $pool->promise();
        $promise->wait();

        return $pairs;
    }
}
