<?php
namespace Lezhnev74\HLSMonitor\Services\CheckStreamAvailable;


use Lezhnev74\HLSMonitor\Data\Playlist\InvalidPlaylistFormat;
use Lezhnev74\HLSMonitor\Data\Stream\Stream;
use Lezhnev74\HLSMonitor\Services\Service;

class CheckStreamAvailable implements Service
{
    private $stream;
    
    
    public function __construct(Stream $stream)
    {
        $this->stream = $stream;
    }
    
    public function execute()
    {
        $chunks = $this->getStreamChunkUrls();
        
        foreach ($chunks as $chunk) {
            $chunk_url = dirname($this->stream->getUrl()) . "/" . $chunk;
            try {
                $content = $this->downloadFewBytes($chunk_url);
            } catch (ChunkIsNotAvailable $e) {
                throw new StreamIsNotAvailable("Chunk is not accessible from the Internet. \r\n Chunk URL: " . $chunk_url . " \r\n Stream URL: " . $this->stream->getUrl());
            }
        }
        
    }
    
    private function getStreamChunkUrls()
    {
        
        $urls = [];
        
        $stream_content = @file_get_contents($this->stream->getUrl());
        if ($stream_content === false) {
            throw new StreamIsNotAvailable("Stream is not available");
        }
        $lines = explode(PHP_EOL, $stream_content);
        
        foreach ($lines as $n => $line) {
            if (preg_match("#\#EXTINF#", $line)) {
                if (!isset($lines[$n + 1])) {
                    throw new InvalidPlaylistFormat("Url has invalid format on line: " . ($n + 1));
                }
                $urls[] = $lines[$n + 1];
            }
        }
        
        return $urls;
        
    }
    
    /**
     * REF: http://stackoverflow.com/a/2033444/1637031
     */
    private function downloadFewBytes(string $url)
    {
        
        // php 5.3+ only
        $writefn = function ($ch, $chunk) {
            static $data = '';
            static $limit = 500; // 500 bytes, it's only a test
            
            $len = strlen($data) + strlen($chunk);
            if ($len >= $limit) {
                $data .= substr($chunk, 0, $limit - strlen($data));
                echo strlen($data), ' ', $data;
                
                return -1;
            }
            
            $data .= $chunk;
            
            return strlen($chunk);
        };
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RANGE, '0-1024'); // Download first 1k bytes
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, $writefn);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new ChunkIsNotAvailable(curl_error($ch));
        }
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code < 200 || $http_code >= 300) {
            throw new ChunkIsNotAvailable("Stream returned HTTP code " . $http_code);
        }
        curl_close($ch);
        
        return $result;
    }
}