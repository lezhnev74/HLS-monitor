<?php

namespace Lezhnev74\HLSMonitor\Data\Chunk;

use Lezhnev74\HLSMonitor\Data\HasStatus;

class Chunk
{
    use HasStatus;
    
    private $url;
    
    public function __construct(string $url)
    {
        
        // Validate Url
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new \InvalidArgumentException("Chunk URL is not valid");
        }
        
        $this->url = $url;
    }
    
    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }
    
}