<?php
namespace Lezhnev74\HLSMonitor\Services\CheckStreamAvailable;


use Lezhnev74\HLSMonitor\Data\Playlist\InvalidPlaylistFormat;
use Lezhnev74\HLSMonitor\Data\Stream\Stream;
use Lezhnev74\HLSMonitor\Services\Downloader\Downloader;
use Lezhnev74\HLSMonitor\Services\Downloader\UrlIsNotAccessible;
use Lezhnev74\HLSMonitor\Services\Service;

class CheckStreamAvailable implements Service
{
    private $stream;
    private $downloader;
    
    
    public function __construct(Stream $stream, Downloader $downloader)
    {
        $this->stream     = $stream;
        $this->downloader = $downloader;
    }
    
    /**
     * @throws StreamIsNotAvailable
     * @return array of Failed chunk URLs
     */
    public function execute()
    {
        $chunks        = $this->getStreamChunkUrls();
        $failed_chunks = [];
        
        foreach($chunks as $chunk) {
            $chunk_url = dirname($this->stream->getUrl()) . "/" . $chunk;
            try {
                $this->downloader->downloadFewBytes(500, $chunk_url);
            } catch(UrlIsNotAccessible $e) {
                $failed_chunks[] = [
                    'reason' => $e->getMessage(),
                    'url' => $chunk_url,
                ];
            }
        }
        
        return $failed_chunks;
    }
    
    /**
     * GET .TS chunks for this stream
     *
     * @return array
     * @throws InvalidPlaylistFormat
     * @throws StreamIsNotAvailable
     */
    private function getStreamChunkUrls()
    {
        
        $urls = [];
        
        try {
            $stream_content = $this->downloader->downloadFullFile($this->stream->getUrl());
        } catch(UrlIsNotAccessible $e) {
            throw new StreamIsNotAvailable("Stream is not available");
        }
        $lines = explode(PHP_EOL, $stream_content);
        
        foreach($lines as $n => $line) {
            if(preg_match("#\#EXTINF#", $line)) {
                if(!isset($lines[ $n + 1 ])) {
                    throw new InvalidPlaylistFormat("Url has invalid format on line: " . ($n + 1));
                }
                $urls[] = $lines[ $n + 1 ];
            }
        }
        
        return $urls;
        
    }
    
}