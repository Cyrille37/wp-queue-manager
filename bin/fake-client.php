#!/bin/env php
<?php

$wp_folder = dirname(__FILE__).'/../../../../';
require_once( $wp_folder.'wp-load.php');

require_once( __DIR__.'/../src/QueueManager.php');
use Cyrille37\WPQueueManager\WPQueueManager ;
use Cyrille37\WPQueueManager\Job ;
use Cyrille37\WPQueueManager\Utils ;

$wpqm = WPQueueManager::getInstance();

for( $i=0; $i<100; $i++ )
    $wpqm->dispatch( new Job(
        Job::TYPE_CALLBACK, 'coucou', ['msg'=>'coucou'])
    );
