<?php
namespace Lezhnev74\HLSMonitor\Services\UrlGatherer;


class ZebraCurlChecker implements GathersUrls
{
    private $zebra_curl;
    
    /**
     * ZebraCurlChecker constructor.
     *
     * @param \Zebra_cURL $zebra
     */
    public function __construct(\Zebra_cURL $zebra)
    {
        $this->zebra_curl = $zebra;
    }
    
    
    function gatherWithoutBody(
        array $urls,
        callable $on_fail_url,
        callable $on_good_url = null,
        int $concurrency
    ) {
        $this->zebra_curl->threads = $concurrency;
        $this->zebra_curl->header($urls, function ($result) use ($on_fail_url, $on_good_url) {
            $this->callback($result, $on_fail_url, $on_good_url);
        });
    }
    
    
    function gatherWithBody(
        array $urls,
        callable $on_fail_url,
        callable $on_good_url = null,
        int $concurrency
    ) {
        $this->zebra_curl->threads = $concurrency;
        $this->zebra_curl->get($urls, function ($result) use ($on_fail_url, $on_good_url) {
            $this->callback($result, $on_fail_url, $on_good_url);
        });
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
        $url         = $result->info['original_url'];
        $http_code   = $result->info['http_code'];
        $http_body   = $result->body;
        $curl_status = $result->response[1];
        
        
        // If HTTP code is not good
        if ($curl_status != CURLE_OK) {
            // If fetching is failed at all
            $on_fail_url($url, "Unable to fetch URL");
        } elseif ($http_code < 200 || $http_code >= 300) {
            $on_fail_url($url, "Response HTTP code is " . $http_code);
        } elseif ($on_good_url) {
            $on_good_url($url, $http_body);
        }
    }
    
}