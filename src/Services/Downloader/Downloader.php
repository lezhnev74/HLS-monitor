<?php

namespace Lezhnev74\HLSMonitor\Services\Downloader;

interface Downloader
{
    // Download functions
    function downloadFewBytes(int $bytes, string $url);
    
    function downloadFullFile(string $url);
    
}