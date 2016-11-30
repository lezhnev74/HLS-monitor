<?php
namespace Lezhnev74\HLSMonitor\Services\Downloader;


class MultithreadedCurlDownloader extends CurlDownloader
{
    private $curl_multithreaded;
    
    public function __construct($max_retries = 0, $timeout_seconds = 5)
    {
        parent::__construct($max_retries, $timeout_seconds);
        
        // TODO Refactor to remove this dependency
        $this->curl_multithreaded          = new \Zebra_cURL();
        $this->curl_multithreaded->threads = 50;
        $this->curl_multithreaded->option(CURLOPT_FOLLOWLOCATION, 0);
    }
    
    
    /**
     * Overload this method to use Multithreaded CURL approach
     *
     * @param          $all_urls
     * @param callable $report_fail_url_closure
     *
     * @return mixed|string
     */
    function getUnavailableUrls($all_urls, callable $report_fail_url_closure)
    {
        $this->curl_multithreaded->header($all_urls, function ($result) use ($report_fail_url_closure) {
            // If HTTP code is not good
            if ($result->info['http_code'] < 200 || $result->info['http_code'] >= 300) {
                $report_fail_url_closure($result->info['original_url'],
                    "ResponseHTTP code is " . $result->info['http_code']);
            } elseif ($result->response[1] != CURLE_OK) {
                // If fetching is failed at all
                $report_fail_url_closure($result->info['original_url'], "Unable to fetch URL");
            }
            
        });
    }
    
}