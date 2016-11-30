<?php

use Lezhnev74\HLSMonitor\Services\Downloader\CurlDownloader;
use Lezhnev74\HLSMonitor\Services\Downloader\UrlIsNotAccessible;

class MultithreadedCurlDownloaderTest extends \PHPUnit\Framework\TestCase
{
    
    function test_multithreaded_downloader_detects_failed_urls()
    {
        $urls        = [
            'https://babystep.tv/en',
            'https://babystep.tv/ru',
            'https://babystep.tv/gg',
        ];
        $failed_urls = [];
        
        $downloader = new \Lezhnev74\HLSMonitor\Services\Downloader\MultithreadedCurlDownloader();
        $downloader->getUnavailableUrls($urls, function ($failed_url) use (&$failed_urls) {
            $failed_urls[] = $failed_url;
        });
        
        $this->assertEquals(1, count($failed_urls));
    }
    
    
    function test_multithreaded_is_faster_that_single_threaded_downloader()
    {
        // I will generate a list with N good URLS and N Fail URLs
        $good_urls    = [];
        $invalid_urls = [];
        $n            = 20;
        for ($i = 0; $i < $n; $i++) {
            $good_urls[]    = 'https://en.wikipedia.org/wiki/HLS?a=' . $i;
            $invalid_urls[] = 'https://en.wikipedia.org/wiki/HLS/' . $i;
        }
        $all_urls = array_merge($good_urls, $invalid_urls);
        
        
        //
        // Run multithreaded downloader
        //
        $mt_failed_urls = [];
        $downloader     = new \Lezhnev74\HLSMonitor\Services\Downloader\MultithreadedCurlDownloader();
        $mt_start_at    = microtime(true);
        $downloader->getUnavailableUrls($all_urls, function ($failed_url, $reason) use (&$mt_failed_urls) {
            $mt_failed_urls[] = $failed_url;
        });
        $mt_ended_at = microtime(true);
        
        $this->assertEquals($n, count($mt_failed_urls));
        
        //
        // Run singlethreaded downloader
        //
        $st_failed_urls = [];
        $downloader     = new CurlDownloader();
        $st_start_at    = microtime(true);
        $downloader->getUnavailableUrls($all_urls, function ($failed_url) use (&$st_failed_urls) {
            $st_failed_urls[] = $failed_url;
        });
        $st_ended_at = microtime(true);
        
        $this->assertEquals($n, count($st_failed_urls));
        
        // Calclate time taken
        $mt_time = $mt_ended_at - $mt_start_at;
        $st_time = $st_ended_at - $st_start_at;
        
        echo("\nMT time taken: " . $mt_time);
        echo("\nST time taken: " . $st_time);
        echo("\n");
        
        $this->assertTrue($mt_time < $st_time);
    }
    
}