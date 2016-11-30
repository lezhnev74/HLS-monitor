<?php
namespace Lezhnev74\HLSMonitor\Services\CheckStreamsAvailable;


use Lezhnev74\HLSMonitor\Data\Playlist\InvalidPlaylistFormat;
use Lezhnev74\HLSMonitor\Data\Stream\Stream;
use Lezhnev74\HLSMonitor\Services\CheckStreamAvailable\StreamIsNotAvailable;
use Lezhnev74\HLSMonitor\Services\Downloader\Downloader;
use Lezhnev74\HLSMonitor\Services\Downloader\UrlIsNotAccessible;
use Lezhnev74\HLSMonitor\Services\Service;

class CheckStreamsAvailable implements Service
{
    private $streams;
    private $downloader;
    
    public function __construct(array $streams, Downloader $downloader)
    {
        $this->streams    = $streams;
        $this->downloader = $downloader;
    }
    
    /**
     * @throws StreamIsNotAvailable
     * @return array of Failed chunk URLs
     */
    public function execute()
    {
        //
        // 1. Get all chunks for all the streams in once
        //
        
        $chunks = [];
        foreach ($this->streams as $stream) {
            // assign chunk to each stream URL
            $chunks[$stream->getUrl()] = $this->getStreamChunkUrls($stream);
        }
        
        $chunk_urls    = [];
        $failed_chunks = [];
        
        foreach ($chunks as $stream_url => $stream_chunks) {
            foreach ($stream_chunks as $chunk_url) {
                $chunk_urls[] = dirname($stream_url) . "/" . $chunk_url;
            }
        }
        
        $this->downloader->getUnavailableUrls($chunk_urls, function ($failed_chunk_url, $reason)
        use (&$failed_chunks, $chunks) {
            
            // find stream url by chunk url
            $stream_url = "Unknown";
            foreach ($chunks as $one_stream_url => $stream_chunks) {
                if (in_array(basename($failed_chunk_url), $stream_chunks)) {
                    $stream_url = $one_stream_url;
                }
            }
            
            
            $failed_chunks[] = [
                'reason'     => $reason,
                'stream_url' => $stream_url,
                'url'        => $failed_chunk_url,
            ];
        });
        
        return $failed_chunks;
    }
    
    /**
     * GET .TS chunks for this stream
     *
     * @return array
     * @throws InvalidPlaylistFormat
     * @throws StreamIsNotAvailable
     */
    private function getStreamChunkUrls(Stream $stream)
    {
        
        $urls = [];
        
        try {
            $stream_content = $this->downloader->downloadFullFile($stream->getUrl());
        } catch (UrlIsNotAccessible $e) {
            throw new StreamIsNotAvailable("Stream is not available: " . $stream->getUrl());
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
    
}