<?php

class PlaylistTest extends \PHPUnit\Framework\TestCase
{
    
    function test_playlist_parses_m3u8_with_no_streams()
    {
        $path = __DIR__ . "/resources/valid_no_streams.m3u8";
        $url  = "http://localhost/playlist.m3u8";
        
        $playlist = new \Lezhnev74\HLSMonitor\Playlist\Playlist(file_get_contents($path), $url);
        $this->assertEquals(0, count($playlist->getStreams()));
    }
    
    function test_playlist_parses_m3u8_with_streams()
    {
        $path = __DIR__ . "/resources/valid.m3u8";
        $url  = "http://localhost/some/playlist.m3u8";
        
        $playlist = new \Lezhnev74\HLSMonitor\Playlist\Playlist(file_get_contents($path), $url);
        $streams  = $playlist->getStreams();
        $this->assertEquals(6, count($streams));
        $this->assertEquals("4345315", $streams[0]->getBandwidth());
        $this->assertEquals("1280x720", $streams[0]->getResolution());
        
    }
    
    function test_playlist_detects_wrong_playlist()
    {
        $this->expectException(\Lezhnev74\HLSMonitor\Playlist\InvalidPlaylistFormat::class);
        $path = __DIR__ . "/resources/invalid.m3u8";
        $url  = "http://localhost/some/playlist.m3u8";
        
        $playlist = new \Lezhnev74\HLSMonitor\Playlist\Playlist(file_get_contents($path), $url);
        
    }
}