<?php

namespace Cyrille37\WPQueueManager;

class Utils
{
    /**
     * Nice formating a message with $items then send to error_log().
     * Simply return if WP_DEBUG is not true.
     * @param mixed ...$items
     * @return void
     */
    public static function debug(...$items)
    {
        if( (! defined('WP_DEBUG')) || (!WP_DEBUG) )
            return;

        $msg = '';
        foreach ($items as $item) {
            switch (gettype($item)) {
                case 'boolean':
                    $msg .= ($item ? 'true' : 'false');
                    break;
                case 'NULL':
                    $msg .= 'null';
                    break;
                case 'integer':
                case 'double':
                case 'float':
                case 'string':
                    $msg .= $item;
                    break;
                default:
                    $msg .= var_export($item, true);
            }
            $msg .= ' ';
        }
        error_log($msg);
    }
}
