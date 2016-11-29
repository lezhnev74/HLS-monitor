<?php

namespace Lezhnev74\HLSMonitor\Console\Command;

use Lezhnev74\HLSMonitor\Services\CheckStreamAvailable\CheckStreamAvailable;
use Lezhnev74\HLSMonitor\Services\CheckStreamAvailable\StreamIsNotAvailable;
use Lezhnev74\HLSMonitor\Services\Downloader\CurlDownloader;
use Lezhnev74\HLSMonitor\Services\Downloader\UrlIsNotAccessible;
use Webmozart\Console\Api\Args\Args;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\IO\IO;

class Playlist extends BaseMonitorCommand
{
    public function executeCommand(Args $args, IO $io, Command $command)
    {
        
        $playlist_url = $args->getArgument('PlaylistUrl');
        
        //
        // 1. Download playlist content
        //
        try {
            
            $io->write('Downloading playlist...');
            $content = $this->downloader->downloadFullFile($playlist_url);
            $io->writeLine('<success>DONE</success>');
            
            //
            // 1.1 Resolve Playlist's IP (for balancing review)
            //
            $playlist_ip = gethostbyname(parse_url($playlist_url, PHP_URL_HOST));
            $io->writeLine("<c2>Playlist is located on: " . $playlist_ip . "</c2>");
            
            //
            // 2. Playlist is downloaded
            //
            $playlist = new \Lezhnev74\HLSMonitor\Data\Playlist\Playlist($content, $playlist_url);
            
            //
            // 3. Get streams
            //
            $streams = $playlist->getStreams();
            $io->writeLine('<success>Found streams in playlist: ' . count($streams) . "</success>");
            
            //
            // 4. Validate stream's chunks
            //
            $io->writeLine('Started checking streams');
            foreach($streams as $stream) {
                $service = new CheckStreamAvailable($stream, $this->downloader);
                try {
                    $failed_chunks = $service->execute();
                    if(count($failed_chunks)) {
                        $msg = "Stream has unaccessible chunks";
                        foreach($failed_chunks as $chunk) {
                            $io->writeLine("Chunk: " . $chunk['url']);
                        }
                        $io->writeLine("<error>" . $msg . "</error>");
                    }
                } catch(StreamIsNotAvailable $e) {
                    $io->writeLine("<error>Stream is not accessible: " . $stream->getUrl() . "</error>");
                }
            }
            $io->writeLine('<success>Checking Streams is DONE</success>');
            
        } catch(UrlIsNotAccessible $e) {
            $io->writeLine('<error>Playlist is not accessible from this node</error>');
            
        } catch(\Exception $e) {
            $io->writeLine('<error>Something nasty happened: ' . $e->getMessage() . '</error>');
        }
        
        return 0;
    }
    
}