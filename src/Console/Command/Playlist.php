<?php

namespace Lezhnev74\HLSMonitor\Console\Command;

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
//        $playlist_urls     = array_filter($all_playlist_urls, function ($url) {
//            if (filter_var($url, FILTER_VALIDATE_URL) === false) {
//                return false;
//            }
//
//            return true;
//        });
//        $bad_playlist_urls = array_diff($all_playlist_urls, $playlist_urls);
//        if (count($bad_playlist_urls)) {
//            foreach ($bad_playlist_urls as $url) {
//                $io->writeLine("<error>Playlist urls which was not recognized</error>");
//            }
//
//            $return_code = 1;
//        }
        
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
            function ($url, $reason) {
                
            },
            function ($url, $body) {
                var_dump($body);
            },
            true // gather body
        );
        $service = get_container()->make(CheckUrls::class, ['request' => $request]);
        $service->execute();
        
        $io->writeLine("Streams fetching is over");
        
        //
        // Temp Reporting
        //
        foreach ($playlists as $playlist) {
            if (!$playlist->isAccessible()) {
                $io->writeLine('<error>Playlisst is not available: ' . $playlist->getUrl() . '</error>');
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