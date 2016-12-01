<?php
namespace Lezhnev74\HLSMonitor\Services\CheckUrls;


class CheckUrlsRequest
{
    private $on_accessible_url;
    private $on_non_accessible_url;
    private $urls = [];
    private $gatherBody;
    
    /**
     * CheckUrlsRequest constructor.
     *
     * @param       $on_complete
     * @param       $on_accessible_url
     * @param       $on_non_accessible_url
     * @param array $urls
     */
    public function __construct(
        array $urls,
        callable $on_non_accessible_url,
        callable $on_accessible_url = null,
        bool $gatherBody = false
    ) {
        //
        // validate all URLs are valid
        //
//        foreach ($urls as $url) {
//            if (filter_var($url, FILTER_VALIDATE_URL) === false) {
//                throw new \InvalidArgumentException("Url is not valid: " . $url);
//            }
//        }
        
        $this->on_accessible_url     = $on_accessible_url;
        $this->on_non_accessible_url = $on_non_accessible_url;
        $this->urls                  = $urls;
        $this->gatherBody            = $gatherBody;
    }
    
    /**
     * @return callable
     */
    public function getOnAccessibleUrl()
    {
        return $this->on_accessible_url;
    }
    
    /**
     * @return callable
     */
    public function getOnNonAccessibleUrl()
    {
        return $this->on_non_accessible_url;
    }
    
    /**
     * @return array
     */
    public function getUrls(): array
    {
        return $this->urls;
    }
    
    /**
     * @return boolean
     */
    public function isGatherBody(): bool
    {
        return $this->gatherBody;
    }
    
    
}