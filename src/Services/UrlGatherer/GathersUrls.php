<?php
namespace Lezhnev74\HLSMonitor\Services\UrlGatherer;


interface GathersUrls
{
    /**
     * Request URL and recieve full body answer
     * will invoike:
     *  $on_good_url($url, $content = "");
     *  $on_fail_url($url, $reason = "");
     *
     * @param array         $urls
     * @param callable      $on_fail_url
     * @param callable|null $on_good_url
     * @param int           $concurrency
     *
     * @return mixed
     */
    function gatherWithBody(
        array $urls,
        callable $on_fail_url,
        callable $on_good_url = null,
        int $concurrency
    );
    
    /**
     * Request URL and recieve only header to make sure URL is accessbile but do not accept body
     *
     * @param array         $urls
     * @param callable      $on_fail_url
     * @param callable|null $on_good_url
     * @param int           $concurrency
     *
     * @return mixed
     */
    function gatherWithoutBody(
        array $urls,
        callable $on_fail_url,
        callable $on_good_url = null,
        int $concurrency
    );
}