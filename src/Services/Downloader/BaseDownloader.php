<?php
namespace Lezhnev74\HLSMonitor\Services\Downloader;


abstract class BaseDownloader implements Downloader
{
    private $retries = [];
    private $max_retries;
    private $timeout_seconds;
    
    /**
     * CurlDownloader constructor.
     *
     * @param int $max_retries     until report failed URL
     * @param int $timeout_seconds how many seconds to wait until next attempt
     */
    public function __construct($max_retries = 0, $timeout_seconds = 5)
    {
        $this->max_retries     = $max_retries;
        $this->timeout_seconds = $timeout_seconds;
    }
    
    
    final public function downloadFewBytes(int $limit, string $url)
    {
        try {
            return $this->downloadBytes($url, $limit);
        } catch(UrlIsNotAccessible $e) {
            if($this->shouldRetry($url)) {
                return $this->downloadFewBytes($limit, $url);
            } else {
                throw $e;
            }
        }
    }
    
    final public function downloadFullFile(string $url)
    {
        try {
            $content = $this->downloadBytes($url);
            return $content;
        } catch(UrlIsNotAccessible $e) {
            if($this->shouldRetry($url)) {
                return $this->downloadFullFile($url);
            } else {
                throw $e;
            }
        }
    }
    
    final protected function shouldRetry(string $url): bool
    {
        // init internal counter
        if(!isset($this->retries[ $url ])) {
            $this->retries[ $url ] = 0;
        }
        
        if($this->retries[ $url ] < $this->max_retries) {
            $this->retries[ $url ]++;
            sleep($this->timeout_seconds);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * @param      $url
     * @param null $limit
     *
     * @throws UrlIsNotAccessible
     * @return mixed
     */
    abstract protected function downloadBytes(string $url, int $limit = null): string;
    
}