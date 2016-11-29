<?php
namespace Lezhnev74\HLSMonitor\Services\Downloader;


class CurlDownloader implements Downloader
{
    public function downloadFewBytes(int $limit, string $url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RANGE, '0-' . ($limit - 1)); // Download first 1k bytes
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        if(curl_errno($ch)) {
            throw new UrlIsNotAccessible(curl_error($ch));
        }
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if($http_code < 200 || $http_code >= 300) {
            throw new UrlIsNotAccessible("HTTP code is " . $http_code . ", but only 2xx are allowed");
        }
        curl_close($ch);
        
        return $result;
    }
    
    public function downloadFullFile(string $url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        if(curl_errno($ch)) {
            throw new UrlIsNotAccessible(curl_error($ch));
        }
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if($http_code < 200 || $http_code >= 300) {
            throw new UrlIsNotAccessible("HTTP code is " . $http_code . ", but only 2xx are allowed");
        }
        
        return $result;
    }
    
    
}