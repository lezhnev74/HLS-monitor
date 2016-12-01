<?php

class ZebraCurlCheckerTest extends \PHPUnit\Framework\TestCase
{
    
    function test_checker_detects_failed_urls()
    {
        $urls        = [
            'https://babystep.tv/en',
            'https://babystep.tv/ru',
            'https://babystep.tv/gg',
        ];
        $failed_urls = [];
        
        $checker = get_container()->get(\Lezhnev74\HLSMonitor\Services\UrlGatherer\ZebraCurlChecker::class);
        $checker->gatherWithoutBody($urls, function ($url, $reason) use (&$failed_urls) {
            $failed_urls[] = $url;
        });
        
        $this->assertEquals(1, count($failed_urls));
    }
    
}