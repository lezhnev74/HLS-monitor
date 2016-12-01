<?php
namespace Lezhnev74\HLSMonitor\Data\Playlist;


use Lezhnev74\HLSMonitor\Data\HasStatus;
use Lezhnev74\HLSMonitor\Data\Stream\Stream;

class Playlist
{
    use HasStatus;
    
    private $playlist_content;
    private $playlist_url;
    private $streams;
    
    public function __construct(string $playlist_content, string $playlist_url)
    {
        $playlist_content = trim($playlist_content);
        $this->validate($playlist_content, $playlist_url);
        $this->playlist_content = $playlist_content;
        $this->playlist_url     = $playlist_url;
        
        $this->streams = $this->makeStreams();
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
     * @return array
     */
    public function getStreams(): array
    {
        return $this->streams;
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
    
    
    /**
     * Will parse playlist content and make streams from it
     *
     * @return array
     * @throws InvalidPlaylistFormat
     */
    private function makeStreams(): array
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
                $resolution = isset($p['resolution']) ? strlen($p['resolution']) ? $p['resolution'] : null : null;
                $bandwidth  = isset($p['bandwidth']) ? strlen($p['bandwidth']) ? $p['bandwidth'] : null : null;
                
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
    
    /**
     * Set content for stream by his URL
     * Will remove old object and will create a new one
     *
     * @param Stream $stream
     * @param string $content
     */
    public function setContentForStreamUrl(string $url, string $content)
    {
        foreach ($this->streams as $key => $stream) {
            if ($stream->getUrl() == $url) {
                $this->streams[$key] = new Stream(
                    $url,
                    $stream->getResolution(),
                    $stream->getBandwidth(),
                    $content
                );
            }
        }
    }
    
    /**
     * @param string $url
     *
     * @return Stream|null
     */
    public function findStreamByUrl(string $url)
    {
        foreach ($this->streams as $key => $stream) {
            if ($stream->getUrl() == $url) {
                return $stream;
            }
        }
        
        return null;
    }
    
    /**
     * @param string $url
     *
     * @return Chunk|null
     */
    public function findChunkByUrl(string $url)
    {
        foreach ($this->streams as $key => $stream) {
            foreach ($stream->getChunks() as $chunk) {
                if ($chunk->getUrl() == $url) {
                    return $chunk;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Return all my stream's chunks
     *
     * @return array
     */
    public function getChunks(): array
    {
        $chunks = [];
        foreach ($this->getStreams() as $stream) {
            $chunks = array_merge($chunks, $stream->getChunks());
        }
        
        return $chunks;
    }
    
}