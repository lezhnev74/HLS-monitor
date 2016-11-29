<?php

namespace Lezhnev74\HLSMonitor\Console\Command;

use Lezhnev74\HLSMonitor\Services\CheckStreamAvailable\CheckStreamAvailable;
use Lezhnev74\HLSMonitor\Services\CheckStreamAvailable\StreamIsNotAvailable;
use Lezhnev74\HLSMonitor\Services\Downloader\CurlDownloader;
use Lezhnev74\HLSMonitor\Services\Downloader\UrlIsNotAccessible;
use Webmozart\Console\Api\Args\Args;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\IO\IO;

abstract class BaseMonitorCommand
{
    protected $downloader;
    
    public function handle(Args $args, IO $io, Command $command)
    {
        
        $retries = $args->getOption('retries');
        $timeout = $args->getOption('timeout');
        //
        // Init downloader with given behaviour
        //
        $this->downloader = new CurlDownloader($retries, $timeout);
        
        //
        // Detect my IP (for logs)
        //
        $my_ip = shell_exec("wget http://ipinfo.io/ip -qO -");
        $io->writeLine("<c2>---------------------------------------------------</c2>");
        $io->writeLine("<c2>Executed on node: " . trim($my_ip) . "</c2>");
        $io->writeLine("<c2>Date of execution: " . date('d.m.Y, H:i T') . "</c2>");
        $io->writeLine("<c2>---------------------------------------------------</c2>");
        $io->writeLine("");
        
        $this->executeCommand($args, $io, $command);
        
        return 0;
    }
    
    abstract protected function executeCommand(Args $args, IO $io, Command $command);
}