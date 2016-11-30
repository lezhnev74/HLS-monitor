<?php

namespace Lezhnev74\HLSMonitor\Data\Stream;

use Lezhnev74\HLSMonitor\Data\HasStatus;
use Lezhnev74\HLSMonitor\Data\Playlist\InvalidPlaylistFormat;

class Stream
{
    use HasStatus;
    
    private $resolution;
    private $bandwidth;
    private $url;
    private $chunks;
    
    /**
     * Stream constructor.
     *
     * @param $resolution
     * @param $bandwidth
     * @param $url
     */
    public function __construct(string $url, string $resolution = null, string $bandwidth = null, $chunks = [])
    {
        
        // Validate Url
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new \InvalidArgumentException("Stream URL is not valid");
        }
        
        $this->resolution = $resolution;
        $this->bandwidth  = $bandwidth;
        $this->url        = $url;
        $this->chunks     = $chunks;
    }
    
    /**
     * Support immutability
     *
     * @param array $chunks
     *
     * @return static
     */
    public function setChunks(array $chunks)
    {
        return new static(
            $this->url,
            $this->resolution,
            $this->bandwidth,
            $chunks
        );
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