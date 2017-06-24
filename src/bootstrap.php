<?php

//
// Make config available globally
//
function get_config(): array
{
    static $config = null;
    if (!$config) {
        $config = include(__DIR__ . "/config.php");
    }
    
    return $config;
}

//
// Make container available globally
//
function get_container()
{
    static $container = null;
    if (!$container) {
        $builder = new \DI\ContainerBuilder();
        $builder->addDefinitions(get_config()['di']);
        
        $container = $builder->build();
    }
    
    return $container;
}


