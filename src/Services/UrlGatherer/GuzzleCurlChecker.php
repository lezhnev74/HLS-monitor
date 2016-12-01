<?php
namespace Lezhnev74\HLSMonitor\Services\UrlGatherer;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\EachPromise;
use Psr\Http\Message\ResponseInterface;

class GuzzleCurlChecker implements GathersUrls
{
    private $client;
    private $concurrency;
    
    public function __construct(Client $client, int $concurrency = 25)
    {
        $this->client      = $client;
        $this->concurrency = $concurrency;
    }
    
    
    function gatherWithoutBody(
        array $urls,
        callable $on_fail_url,
        callable $on_good_url = null
    ) {
        $promises = (function () use ($urls, $on_fail_url, $on_good_url) {
            foreach ($urls as $url) {
                yield $this->client->requestAsync('HEAD', $url, [
                    'connect_timeout' => 10,
                    'timeout'         => 10,
                ])->then(
                    function (ResponseInterface $res) use ($url, $on_good_url) {
                        // on good
                        $on_good_url($url, "");
                    },
                    function (RequestException $e) use ($url, $on_fail_url) {
                        // on bad
                        $on_fail_url($url, $e->getMessage());
                    }
                );
            }
        })();
        
        (new EachPromise($promises, [
            'concurrency' => $this->concurrency,
        ]))->promise()->wait();
    }
    
    
    function gatherWithBody(
        array $urls,
        callable $on_fail_url,
        callable $on_good_url = null
    ) {
        $promises = (function () use ($urls, $on_fail_url, $on_good_url) {
            foreach ($urls as $url) {
                yield $this->client->requestAsync('GET', $url, [
                    'connect_timeout' => 10,
                ])->then(
                    function (ResponseInterface $res) use ($url, $on_good_url) {
                        // on good
                        $on_good_url($url, $res->getBody()->getContents());
                    },
                    function (RequestException $e) use ($url, $on_fail_url) {
                        // on bad
                        $on_fail_url($url, $e->getMessage());
                    }
                );
            }
        })();
        
        (new EachPromise($promises, [
            'concurrency' => $this->concurrency,
        ]))->promise()->wait();
        
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