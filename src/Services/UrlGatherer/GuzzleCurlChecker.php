<?php
namespace Lezhnev74\HLSMonitor\Services\UrlGatherer;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Promise\EachPromise;
use GuzzleHttp\Psr7\Request;
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
        $handled  = 0;
        $promises = (function () use ($urls, $on_fail_url, $on_good_url, &$handled) {
            foreach ($urls as $url) {
                yield function () use ($url, $on_good_url, $on_fail_url, &$handled, &$urls) {
                    return $this->client->headAsync($url, [
                        'connect_timeout' => 10,
                        'timeout'         => 30,
                        'stream'          => true,
                        'verify'          => false,
                        'allow_redirects' => false,
                    ])->then(
                        function (ResponseInterface $res) use ($url, $on_good_url, &$handled, &$urls) {
                            var_dump($handled++ . " of " . count($urls));
                            // on good
                            $on_good_url($url, "");
                            
                        },
                        function (RequestException $e) use ($url, $on_fail_url, &$handled, &$urls) {
                            var_dump($handled++ . " of " . count($urls));
                            // on bad
                            $on_fail_url($url, $e->getMessage());
                            
                        }
                    );
                };
            }
        })();
        
        
        $pool    = new Pool($this->client, $promises, [
            'concurrency' => $this->concurrency,
            'fulfilled'   => function ($response, $index) {
                var_dump('fullfilled');
            },
            'rejected'    => function ($reason, $index) {
                var_dump('rejected');
            },
        ]);
        $promise = $pool->promise();
        $promise->wait();
        
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
                    'timeout'         => 30,
                    'stream'          => true,
                    'verify'          => false,
                    'allow_redirects' => false,
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
    
    
}