<?php

namespace Cyptalt\Exchange;

use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
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
    private $client;    

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
     * Get API Url from pair config.
     * 
     * @param  array $pairs
     */
    abstract public function getUrl($pairs);
    
    /**
     * Get each value from API result.
     * 
     * @param  array  $pairs
     * @param  string $jsonKey
     */
    abstract public function parseResult($pairs, $jsonKey);

    /**
     * According to exchange API, normalize pair config.
     * 
     * @param  array $pairs
     * @return array
     */
    public function normalizePairs($pairs)
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
