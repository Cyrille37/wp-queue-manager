<?php
namespace Cyrille37\WPQueueManager ;

interface JobListener
{
    /**
     * Update a job status.
     *
     * @param string $jobKey
     * @param Array $status
     * @return void
     */
    public function statusUpdate( $jobKey, Array $status );
    /**
     * Update a job status.
     *
     * @param string $jobKey
     * @return Array $status
     */
    public function statusGet( $jobKey );

}
