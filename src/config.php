<?php

return [
    // IOC definitions
    'di' => [
        \Lezhnev74\HLSMonitor\Services\UrlGatherer\ZebraCurlChecker::class => DI\factory(function () {
            $zebra_curl          = new \Zebra_cURL();
            $zebra_curl->threads = 50;
            $zebra_curl->option(CURLOPT_FOLLOWLOCATION, 0);
            
            $instance = new \Lezhnev74\HLSMonitor\Services\UrlGatherer\ZebraCurlChecker($zebra_curl);
            
            return $instance;
        }),
        
        \Lezhnev74\HLSMonitor\Services\UrlGatherer\GuzzleCurlChecker::class => DI\factory(function () {
            
            $guzzle_cli  = new \GuzzleHttp\Client();
            $concurrency = 30;
            $instance    = new \Lezhnev74\HLSMonitor\Services\UrlGatherer\GuzzleCurlChecker($guzzle_cli, $concurrency);
            
            return $instance;
        }),
        
        \Lezhnev74\HLSMonitor\Services\UrlGatherer\GathersUrls::class =>
            DI\get(\Lezhnev74\HLSMonitor\Services\UrlGatherer\GuzzleCurlChecker::class),
        //DI\get(\Lezhnev74\HLSMonitor\Services\UrlGatherer\ZebraCurlChecker::class),
    ],
];