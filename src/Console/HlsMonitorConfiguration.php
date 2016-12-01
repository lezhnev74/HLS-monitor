<?php

namespace Lezhnev74\HLSMonitor\Console;

use Lezhnev74\HLSMonitor\Console\Command\Playlist;
use Lezhnev74\HLSMonitor\Services\Downloader\CurlDownloader;
use Webmozart\Console\Api\Args\Format\Argument;
use Webmozart\Console\Api\Args\Format\Option;
use Webmozart\Console\Api\Formatter\Style;
use Webmozart\Console\Config\DefaultApplicationConfig;

class HlsMonitorConfiguration extends DefaultApplicationConfig
{
    protected function configure()
    {
        parent::configure();
        
        $this
            ->setName('hls-monitor')
            // command playlist
            ->beginCommand('playlists')
            ->setDescription("Validate M3U8 palylist and it's streams for accessibility")
            ->setHandler(new Playlist())
            ->addArgument('PlaylistUrls', Argument::REQUIRED, 'The M3U8-Playlist URLs to evaluate (comma separated)')
            ->addOption('retries', 'r', Option::OPTIONAL_VALUE,
                'The maximum number of retries before considering URL as unaccessible', 3)
            ->addOption('timeout', 't', Option::OPTIONAL_VALUE,
                'The number of seconds to wait between retries', 5)
            ->end()
            ->addStyle(Style::tag('success')->fgGreen());
        
    }
}