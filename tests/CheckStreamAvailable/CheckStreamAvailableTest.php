<?php

use Lezhnev74\HLSMonitor\Data\Stream\Stream;
use Lezhnev74\HLSMonitor\Services\CheckStreamAvailable\CheckStreamAvailable;
use Lezhnev74\HLSMonitor\Services\Downloader\CurlDownloader;
use Lezhnev74\HLSMonitor\Services\Downloader\Downloader;
use Lezhnev74\HLSMonitor\Services\Downloader\UrlIsNotAccessible;

class CheckStreamAvailableTest extends \PHPUnit\Framework\TestCase
{
    
    function test_stream_url_is_not_available()
    {
        $this->expectException(\Lezhnev74\HLSMonitor\Services\CheckStreamAvailable\StreamIsNotAvailable::class);
        $url = "http://ABSENT/playlist.m3u8";
        
        $stream     = new \Lezhnev74\HLSMonitor\Data\Stream\Stream($url);
        $downloader = new CurlDownloader();
        
        $service = new \Lezhnev74\HLSMonitor\Services\CheckStreamAvailable\CheckStreamAvailable($stream, $downloader);
        $service->execute();
    }
    
    function test_stream_url_does_not_return_code_200()
    {
        $this->expectException(\Lezhnev74\HLSMonitor\Services\CheckStreamAvailable\StreamIsNotAvailable::class);
        $url = "http://localhost/playlist.m3u8";
        
        $stream     = new \Lezhnev74\HLSMonitor\Data\Stream\Stream($url);
        $downloader = new CurlDownloader();
        
        $service = new \Lezhnev74\HLSMonitor\Services\CheckStreamAvailable\CheckStreamAvailable($stream, $downloader);
        $service->execute();
    }
    
    
    function test_stream_url_returns_200()
    {
        // Hopefully, this URL won't change
        
        $url     = "http://akamai.streamroot.edgesuite.net/vodorigin/tos.mp4/playlist.m3u8";
        $content = file_get_contents($url);
        
        $playlist   = new \Lezhnev74\HLSMonitor\Data\Playlist\Playlist($content, $url);
        $stream     = $playlist->getStreams()[0];
        $downloader = new CurlDownloader();
        
        $service = new \Lezhnev74\HLSMonitor\Services\CheckStreamAvailable\CheckStreamAvailable($stream, $downloader);
        $service->execute();
    }
    
}