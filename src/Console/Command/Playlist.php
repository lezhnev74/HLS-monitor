<?php

namespace Lezhnev74\HLSMonitor\Console\Command;

use Lezhnev74\HLSMonitor\Data\Playlist\InvalidPlaylistFormat;
use Lezhnev74\HLSMonitor\Services\CheckStreamAvailable\CheckStreamAvailable;
use Lezhnev74\HLSMonitor\Services\CheckStreamAvailable\StreamIsNotAvailable;
use Lezhnev74\HLSMonitor\Services\CheckStreamsAvailable\CheckStreamsAvailable;
use Lezhnev74\HLSMonitor\Services\CheckUrls\CheckUrls;
use Lezhnev74\HLSMonitor\Services\CheckUrls\CheckUrlsRequest;
use Lezhnev74\HLSMonitor\Services\Downloader\CurlDownloader;
use Lezhnev74\HLSMonitor\Services\Downloader\UrlIsNotAccessible;
use Webmozart\Console\Api\Args\Args;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\IO\IO;

class Playlist extends BaseMonitorCommand
{
    public function executeCommand(Args $args, IO $io, Command $command)
    {
        
        $return_code = 0;
        $retries     = $args->getOption('retries');
        $timeout     = $args->getOption('timeout');
        
        //
        // 1. Get all playlist URLs
        //
        $all_playlist_urls = explode(",", $args->getArgument('PlaylistUrls'));
        $playlist_urls     = array_map(function ($url) {
            return trim($url);
        }, $all_playlist_urls);
        
        //
        // 2. Retrieve all Playlists contents and make playlist models
        //
        // TODO refactor to use DI
        $playlists = [];
        $request   = new CheckUrlsRequest(
            $playlist_urls,
            //
            // Handler for bad URL
            //
            function ($url, $reason) use (&$playlists, $io) {
                // URL is not good
                $io->writeLine("<error>Playlist URL: " . $url . "</error>");
                $io->writeLine("  \\--" . $reason);
            },
            //
            // Handler for good URL
            //
            function ($url, $body) use (&$playlists) {
                // URL is good
                $playlist = new \Lezhnev74\HLSMonitor\Data\Playlist\Playlist($body, $url);
                $playlist->reportAsAccessible();
                
                $playlists[] = $playlist;
            },
            true // gather body
        );
        $service   = get_container()->make(CheckUrls::class, ['request' => $request]);
        $service->execute();
        
        $io->writeLine("Playlists fetching is over");
        
        //
        // 3. Prepare all stream URLs for all playlists
        //
        $stream_urls = [];
        foreach ($playlists as $playlist) {
            foreach ($playlist->getStreams() as $stream) {
                $stream_urls[] = $stream->getUrl();
            }
        }
        
        //
        // 3.1 Fetch all streams in one service call
        //
        $request = new CheckUrlsRequest(
            $stream_urls,
            function ($url, $reason) use ($playlists) {
                foreach ($playlists as $playlist) {
                    if ($stream = $playlist->findStreamByUrl($url)) {
                        $stream->reportAsNotAccessible($reason);
                        break;
                    }
                }
            },
            function ($url, $body) use ($playlists) {
                // find which playlist owns this stream's url
                foreach ($playlists as $playlist) {
                    try {
                        $playlist->setContentForStreamUrl($url, $body);
                    } catch (InvalidPlaylistFormat $e) {
                        $stream = $playlist->findStreamByUrl($url);
                        $stream->reportAsNotAccessible("Bad content of the stream (limited to 100 chars):\n"
                                                       . substr($body, 0, 100));
                    }
                }
            },
            true // gather body
        );
        $service = get_container()->make(CheckUrls::class, ['request' => $request]);
        $service->execute();
        
        $io->writeLine("Streams fetching is over");
        
        //
        // 4. Prepare all Chunks URLs
        //
        $chunk_urls = [];
        foreach ($playlists as $playlist) {
            foreach ($playlist->getChunks() as $chunk) {
                $chunk_urls[] = $chunk->getUrl();
            }
        }
        
        //
        // 4.1.  Fetch all CHunk URLs over one service call
        //
        $request = new CheckUrlsRequest(
            $chunk_urls,
            function ($url, $reason) use ($playlists) {
                foreach ($playlists as $playlist) {
                    if ($chunk = $playlist->findChunkByUrl($url)) {
                        $chunk->reportAsNotAccessible($reason);
                        break;
                    }
                }
            },
            function ($url, $body) use ($playlists) {
                // find which playlist owns this stream's url
                foreach ($playlists as $playlist) {
                    if ($chunk = $playlist->findChunkByUrl($url)) {
                        $chunk->reportAsAccessible();
                        break;
                    }
                }
            },
            false // skip body to save speed
        );
        $service = get_container()->make(CheckUrls::class, ['request' => $request]);
        $service->execute();
        
        $io->writeLine("Chunks fetching is over");
        
        
        //
        // Temp Reporting
        //
        foreach ($playlists as $playlist) {
            if (!$playlist->isAccessible()) {
                $io->writeLine('<error>Playlist is not available: ' . $playlist->getUrl() . '</error>');
            } else {
                // check streams of the playlist
                $bad_streams = [];
                foreach ($playlist->getStreams() as $stream) {
                    if (!$stream->isAccessible() && $stream->isCheckedForAccessibility()) {
                        $bad_streams[$stream->getUrl()] = [
                            'stream'     => $stream,
                            'bad_chunks' => [],
                        ];
                    } else {
                        // check chunks of the stream
                        foreach ($stream->getChunks() as $chunk) {
                            if (!$chunk->isAccessible() && $chunk->isCheckedForAccessibility()) {
                                
                                if (!isset($bad_streams[$stream->getUrl()])) {
                                    $bad_streams[$stream->getUrl()] = [
                                        'stream'     => $stream,
                                        'bad_chunks' => [],
                                    ];
                                }
                                $bad_streams[$stream->getUrl()]['bad_chunks'][] = $chunk;
                                
                            }
                        }
                    }
                }
                
                if (count($bad_streams)) {
                    $io->writeLine('<error>Playlist has bad Streams:</error>');
                    $io->writeLine('<c1>  \-- Playlist URL: ' . $playlist->getPlaylistUrl()."</c1>");
                    
                    foreach ($bad_streams as $stream_url => $stream_data) {
                        $bad_stream = $stream_data['stream'];
                        $io->writeLine('<c2>      \-- Stream url: ' . $bad_stream->getUrl()."</c2>");
                        
                        if (!$bad_stream->isAccessible() && $bad_stream->isCheckedForAccessibility()) {
                            $io->writeLine('          \-- Reason: ' . $bad_stream->getNotAccessibleReason());
                        } elseif (count($stream_data['bad_chunks'])) {
                            foreach ($stream_data['bad_chunks'] as $chunk) {
                                $io->writeLine('          \-- Chunk: ' . $chunk->getUrl());
                                $io->writeLine('              \-- Reason: ' . $chunk->getNotAccessibleReason());
                            }
                        }
                    }
                }
            }
        }
        
        
        return $return_code;
    }
    
    
    /**
     * Will group by key
     *
     * @param $array
     */
    private function groupArrayByKey($key, $array)
    {
        $return = [];
        
        foreach ($array as $item) {
            if (!isset($return[$item[$key]])) {
                $return[$item[$key]] = [];
            }
            
            $return[$item[$key]][] = $item;
        }
        
        return $return;
    }
    
}