<?php

namespace Lezhnev74\HLSMonitor\Services\Downloader;

interface Downloader
{
    
    public function downloadFewBytes(int $bytes, string $url);
    
    public function downloadFullFile(string $url);
    
}