#!/bin/env php
<?php

$wp_folder = dirname(__FILE__).'/../../../../';
require_once( $wp_folder.'wp-load.php');

require_once( __DIR__.'/../src/QueueManager.php');
use Cyrille37\WPQueueManager\WPQueueManager ;
use Cyrille37\WPQueueManager\Job ;
use Cyrille37\WPQueueManager\Utils ;

$wpqm = WPQueueManager::getInstance();


function coucou(...$args)
{
    Utils::debug(__FUNCTION__);
    //Utils::debug(__FUNCTION__, ['args'=>$args]);

    $jobKey = $args[1];
    $wpqm = WPQueueManager::getInstance();
    for( $i=0; $i<5; $i++ )
    {
        $wpqm->statusUpdate( $jobKey, ['progress'=>($i+1), 'time'=>microtime(true)]);
        sleep( 1 );
    }
    $status = $wpqm->statusGet( $jobKey );
    //Utils::debug(__FUNCTION__, ['status'=>$status]);
}

$wpqm->run();
