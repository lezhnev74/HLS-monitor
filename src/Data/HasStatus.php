<?php

namespace Lezhnev74\HLSMonitor\Data;

trait HasStatus
{
    
    protected $accessibility_status = 0;
    protected $reason;
    
    function reportAsAccessible()
    {
        $this->accessibility_status = 1;
    }
    
    function reportAsNotAccessible($reason = '')
    {
        $this->accessibility_status = 2;
        $this->reason               = $reason;
    }
    
    function isAccessible(): bool
    {
        return $this->accessibility_status == 1;
    }
    
    function isCheckedForAccessibility(): bool
    {
        return $this->accessibility_status != 0;
    }
    
    /**
     * @return mixed
     */
    public function getNotAccessibleReason()
    {
        return $this->reason;
    }
    
    
}