<?php

namespace Lezhnev74\HLSMonitor\Data;

trait HasStatus
{
    
    protected $accessibility_status = 0;
    
    function reportAsAccessible()
    {
        $this->accessibility_status = 1;
    }
    
    function reportAsNotAccessible()
    {
        $this->accessibility_status = 2;
    }
    
    function isAccessible(): bool
    {
        return $this->accessibility_status == 1;
    }
    
    function isCheckedForAccessibility(): bool
    {
        return $this->accessibility_status != 0;
    }
    
}