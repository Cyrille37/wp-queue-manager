<?php
/**
 * Plugin Name: WP Queue Manager
 * Description: Job Queue for WordPress
 * Version: 0.1
 * Author: Cyrille37
 * Author URI: http://github.com/Cyrille37
 * License: MIT
 */

require_once(__DIR__.'/src/QueueManager.php');

use Cyrille37\WPQueueManager\WPQueueManager ;

WPQueueManager::getInstance()
    ->wordpress();
