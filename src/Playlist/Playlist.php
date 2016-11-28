<?php
namespace Lezhnev74\HLSMonitor\Playlist;


use Lezhnev74\HLSMonitor\Stream\Stream;

class Playlist
{
    private $playlist_content;
    private $playlist_url;
    
    public function __construct(string $playlist_content, string $playlist_url)
    {
        $playlist_content = trim($playlist_content);
        $this->validate($playlist_content, $playlist_url);
        $this->playlist_content = $playlist_content;
        $this->playlist_url     = $playlist_url;
    }
    
    /**
     * @return string
     */
    public function getPlaylistContent(): string
    {
        return $this->playlist_content;
    }
    
    /**
     * @return string
     */
    public function getPlaylistUrl(): string
    {
        return $this->playlist_url;
    }
    
    
    /**
     * Ref: https://en.wikipedia.org/wiki/M3U
     *
     * @param string $content
     */
    private function validate(string $content, string $url)
    {
        $lines = explode(PHP_EOL, $content);
        
        // 1. Header is required
        if ($lines[0] != "#EXTM3U") {
            throw new InvalidPlaylistFormat();
        }
        
        // Validate Url
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new \InvalidArgumentException("Playlist URL is not valid");
        }
    }
    
    public function getStreams(): array
    {
        $lines   = explode(PHP_EOL, $this->playlist_content);
        $streams = [];
        
        foreach ($lines as $n => $line) {
            
            if (preg_match(
                "#^\#EXT-X-STREAM-INF:?(BANDWIDTH=(?<bandwidth>[\d]+))?(,)?(RESOLUTION=(?<resolution>[\d]+x[\d]+))?#",
                $line,
                $p)
            ) {
                if (!isset($lines[$n + 1])) {
                    throw new InvalidPlaylistFormat("Frament [" . $line . "] is not followed by the file link");
                }
                $url = $this->makeStreamUrl($lines[$n + 1]);
                // do not set empty row, transform those to NULLs
                $resolution = $p['resolution'] ? strlen($p['resolution']) ? $p['resolution'] : null : null;
                $bandwidth  = $p['bandwidth'] ? strlen($p['bandwidth']) ? $p['bandwidth'] : null : null;
                
                $streams[] = new Stream($url, $resolution, $bandwidth);
            }
            
            
        }
        
        return $streams;
    }
    
    private function makeStreamUrl($stream_file_url)
    {
        $url = dirname($this->playlist_url) . "/" . $stream_file_url;
        
        return $url;
    }
}