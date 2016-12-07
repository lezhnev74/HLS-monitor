<?php
namespace Lezhnev74\HLSMonitor\Services\CheckUrls;

use Lezhnev74\HLSMonitor\Services\Service;
use Lezhnev74\HLSMonitor\Services\UrlGatherer\GathersUrls;


/**
 * Class CheckUrls
 * Service is designed to act in Async way
 * Underneath it could use multithreaded ways of checking urls
 *
 * @package Lezhnev74\HLSMonitor\Services
 */
class CheckUrls implements Service
{
    private $request;
    private $gatherer;
    
    public function __construct(
        GathersUrls $gatherer,
        CheckUrlsRequest $request
    ) {
        $this->request  = $request;
        $this->gatherer = $gatherer;
    }
    
    
    /**
     * Check given URLS and report
     */
    function execute()
    {
        if ($this->request->isGatherBody()) {
            $this->gatherer->gatherWithBody(
                $this->request->getUrls(),
                $this->request->getOnNonAccessibleUrl(),
                $this->request->getOnAccessibleUrl(),
                $this->request->getConcurrency()
            );
        } else {
            $this->gatherer->gatherWithoutBody(
                $this->request->getUrls(),
                $this->request->getOnNonAccessibleUrl(),
                $this->request->getOnAccessibleUrl(),
                $this->request->getConcurrency()
            );
        }
        
    }
    
}