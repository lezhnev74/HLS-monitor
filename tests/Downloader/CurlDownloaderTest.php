<?php

use Lezhnev74\HLSMonitor\Services\Downloader\CurlDownloader;
use Lezhnev74\HLSMonitor\Services\Downloader\UrlIsNotAccessible;

class CurlDownloaderTest extends \PHPUnit\Framework\TestCase
{
    
    function test_downloader_rises_exception_on_fail()
    {
        $this->expectException(UrlIsNotAccessible::class);
        
        $url        = "http://localhost/playlist.m3u8";
        $downloader = new CurlDownloader();
        $downloader->downloadFullFile($url);
    }
    
    function test_downloader_downloads_full_file()
    {
        $url        = "http://akamai.streamroot.edgesuite.net/vodorigin/tos.mp4/playlist.m3u8";
        $downloader = new CurlDownloader();
        $content    = $downloader->downloadFullFile($url);
        
        $this->assertEquals(127, strlen($content));
    }
    
    function test_downloader_downloads_file_partially()
    {
        $limit      = 10;
        $url        = "http://akamai.streamroot.edgesuite.net/vodorigin/tos.mp4/playlist.m3u8";
        $downloader = new CurlDownloader();
        $content    = $downloader->downloadFewBytes($limit, $url);
        
        $this->assertEquals($limit, strlen($content));
    }
    
    function test_downloader_will_retry_on_fail()
    {
        $retries    = 1;
        $timeout    = 1;
        $downloader = $this->getMockBuilder(CurlDownloader::class)
                           ->setConstructorArgs([
                                                    $retries,
                                                    $timeout,
                                                ])
                           ->getMock();
        
        $downloader->max_retries = $retries;
        
        $callback = function($arg) use ($retries) {
            static $made_requests = 0;
            $made_requests++;
            // last retry must succeed
            if($made_requests == ($retries + 1)) {
                return "GOOD";
            } else {
                throw new UrlIsNotAccessible();
            }
        };
        $downloader->method('downloadBytes')->will($this->returnCallback($callback));
        $content = $downloader->downloadFullFile("any");
        
        $this->assertEquals("GOOD", $content);
        
    }
    
}