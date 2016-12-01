<?php
namespace Lezhnev74\HLSMonitor\Services\UrlGatherer;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

class GuzzleCurlChecker implements GathersUrls
{
    private $client;
    private $concurrency;
    
    public function __construct(Client $client, int $concurency = 25)
    {
        $this->client      = $client;
        $this->concurrency = $concurency;
    }
    
    
    function gatherWithoutBody(
        array $urls,
        callable $on_fail_url,
        callable $on_good_url = null
    ) {
        $promises = [];
        foreach ($urls as $url) {
            $promise = $this->client->headAsync($url);
            $promise->then(function (ResponseInterface $res) use ($url, $on_good_url) {
                // on good
                $on_good_url($url, $res->getBody());
            }, function (RequestException $e) use ($url, $on_fail_url) {
                // on bad
                $on_fail_url($url, $e->getMessage());
            });
            $promises[] = $promise;
        }
        
        $results = \GuzzleHttp\Promise\settle($promises)->wait();
    }
    
    
    function gatherWithBody(
        array $urls,
        callable $on_fail_url,
        callable $on_good_url = null
    ) {
        $promises = [];
        foreach ($urls as $url) {
            $promise = $this->client->getAsync($url);
            $promise->then(function (ResponseInterface $res) use ($url, $on_good_url) {
                // on good
                $on_good_url($url, $res->getBody());
            }, function (RequestException $e) use ($url, $on_fail_url) {
                // on bad
                $on_fail_url($url, $e->getMessage());
            });
            $promises[] = $promise;
        }
        
        $results = \GuzzleHttp\Promise\settle($promises)->wait();
    }
    
    /**
     * Callback for each completed check on single URL
     *
     * @param $result
     * @param $on_fail_url
     * @param $on_good_url
     */
    private function callback($result, $on_fail_url, $on_good_url)
    {
        
    }
    
}