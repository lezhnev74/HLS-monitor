<?php

namespace Lezhnev74\HLSMonitor\Data\Stream;

use Lezhnev74\HLSMonitor\Data\Chunk\Chunk;
use Lezhnev74\HLSMonitor\Data\HasStatus;
use Lezhnev74\HLSMonitor\Data\Playlist\InvalidPlaylistFormat;

class Stream
{
    use HasStatus;
    
    private $resolution;
    private $bandwidth;
    private $url;
    private $content;
    private $chunks;
    
    /**
     * Stream constructor.
     *
     * @param $resolution
     * @param $bandwidth
     * @param $url
     */
    public function __construct(
        string $url,
        string $resolution = null,
        string $bandwidth = null,
        string $content = null
    ) {
        
        $this->resolution = $resolution;
        $this->bandwidth  = $bandwidth;
        $this->url        = $url;
        $this->content    = $content;
        
        $this->validateUrl();
        if ($content) {
            $this->validateContent();
            $this->chunks = $this->makeChunks();
        } else {
            $this->chunks = [];
        }
    }
    
    /**
     * Ref: https://en.wikipedia.org/wiki/M3U
     */
    private function validateUrl()
    {
        // Validate Url
        if (filter_var($this->url, FILTER_VALIDATE_URL) === false) {
            throw new \InvalidArgumentException("Stream URL is not valid");
        }
        
    }
    
    private function validateContent()
    {
        // Validate content
        $lines = explode(PHP_EOL, $this->content);
        
        // 1. Header is required
        if ($lines[0] != "#EXTM3U") {
            throw new InvalidPlaylistFormat();
        }
    }
    
    
    /**
     * Will parse playlist content and make streams from it
     *
     * @return array
     * @throws InvalidPlaylistFormat
     */
    private function makeChunks(): array
    {
        $lines  = explode(PHP_EOL, $this->content);
        $chunks = [];
        
        foreach ($lines as $n => $line) {
            
            if (preg_match("#^\#EXTINF#", $line, $p)) {
                if (!isset($lines[$n + 1])) {
                    throw new InvalidPlaylistFormat("Frament [" . $line . "] is not followed by the file link");
                }
                $url = $this->makeChunkUrl($lines[$n + 1]);
                
                $chunks[] = new Chunk($url);
            }
            
        }
        
        return $chunks;
    }
    
    private function makeChunkUrl($chunk_file_url)
    {
        $url = dirname($this->url) . "/" . $chunk_file_url;
        
        return $url;
    }
    
    
    /**
     * @return mixed
     */
    public function getChunks()
    {
        return $this->chunks;
    }
    
    
    /**
     * @return string
     */
    public function getResolution(): string
    {
        return $this->resolution;
    }
    
    /**
     * @return string
     */
    public function getBandwidth(): string
    {
        return $this->bandwidth;
    }
    
    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }
    
}