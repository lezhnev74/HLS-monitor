<?php

namespace Lezhnev74\HLSMonitor\Services\Downloader;

interface Downloader
{
    // Download functions
    function downloadFewBytes(int $bytes, string $url);
    
    function downloadFullFile(string $url);
    
    // Will check each URL one by one and report failed to closure
    function getUnavailableUrls($all_urls, callable $report_fail_url_closure);
}