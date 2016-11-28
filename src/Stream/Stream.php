<?php

namespace Lezhnev74\HLSMonitor\Stream;

class Stream
{

    private $resolution;
    private $bandwidth;
    private $url;

    /**
     * Stream constructor.
     * @param $resolution
     * @param $bandwidth
     * @param $url
     */
    public function __construct(string $url, string $resolution = null, string $bandwidth = null)
    {

        // Validate Url
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new \InvalidArgumentException("Stream URL is not valid");
        }

        $this->resolution = $resolution;
        $this->bandwidth = $bandwidth;
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getResolution(): string
    {
        return $this->resolution;
    }

    /**
     * @return string
     */
    public function getBandwidth(): string
    {
        return $this->bandwidth;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }


}