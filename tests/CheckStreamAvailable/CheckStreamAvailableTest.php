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
    
    function test_checker_will_detect_invalid_chunks()
    {
        //$playlist_url      = "http://akamai.streamroot.edgesuite.net/vodorigin/tos.mp4/playlist.m3u8";
        $stream_url        = "http://akamai.streamroot.edgesuite.net/vodorigin/tos.mp4/chunklist.m3u8";
        $failed_chunk_urls = [
            "http://akamai.streamroot.edgesuite.net/vodorigin/tos.mp4/media_0.ts",
            "http://akamai.streamroot.edgesuite.net/vodorigin/tos.mp4/media_8.ts",
        ];
        $stream            = new Stream($stream_url);
        
        // Set return values for downloader
        
        $callback_full_file = function($arg) use ($stream_url, $failed_chunk_urls) {
            if($arg == $stream_url) {
                return file_get_contents(__DIR__ . "/../resources/fake_stream/chunks.m3u8");
            }
        };
        $callback_partial   = function($bytes, $url) use ($stream_url, $failed_chunk_urls) {
            if(in_array($url, $failed_chunk_urls)) {
                throw new UrlIsNotAccessible("This url is not accessible");
            }
        };
        
        
        $downloader_stub = $this->createMock(Downloader::class);
        $downloader_stub->method('downloadFullFile')->will($this->returnCallback($callback_full_file));
        $downloader_stub->method('downloadFewBytes')->will($this->returnCallback($callback_partial));
        
        // Validate data
        
        $service       = new CheckStreamAvailable($stream, $downloader_stub);
        $failed_chunks = $service->execute();
        
        $this->assertEquals(count($failed_chunk_urls), count($failed_chunks));
        foreach($failed_chunks as $failed_chunk) {
            $this->assertTrue(in_array($failed_chunk['url'], $failed_chunk_urls));
        }
        
    }
}