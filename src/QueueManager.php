<?php
namespace Cyrille37\WPQueueManager ;

require_once(__DIR__.'/Utils.php');
require_once(__DIR__.'/Job.php');
require_once(__DIR__.'/JobListener.php');

/**
 * 3 master methods:
 * - wordpress: for the WP plugin
 * - dispatch: to push a job in the queue
 * - run: to dequeue and run jobs
 */
class WPQueueManager implements JobListener
{
    const WPOPT_JOB = '_wpqm-job:' ;
    const WPOPT_JOB_LOCK = '_wpqm-job-lock:' ;
    const WPOPT_JOB_STATUS = '_wpqm-job-status:' ;

    protected function __construct()
    {
    }

    /**
     * Singleton access.
     *
     * @return WPQueueManager
     */
    public static function getInstance()
    {
        static $instance ;
        if( empty($instance) )
            $instance = new WPQueueManager();
        return $instance ;
    }

    public function wordpress()
    {
        Utils::debug(__METHOD__);
    }

    public function dispatch( Job $job )
    {
        /**
         * @var \wpdb $wpdb ;
         */
        global $wpdb ;
        Utils::debug(__METHOD__);
        try
        {
            // key must by <= 191 bytes length because of `option_name VARCHAR(191)`.
            $key = self::WPOPT_JOB . $job->getKey() ;
            $value = json_encode( $job );
            $result = $wpdb->query(
                $wpdb->prepare(
                    'INSERT INTO `'.$wpdb->options.'` (`option_name`,`option_value`,`autoload`) VALUES (%s, %s, %s)',
                    $key, $value, $autoload=false
                )
            );
            Utils::debug(__METHOD__, ['result'=>$result]);
        }
        catch( \Exception $ex )
        {

        }
    }

    public function run()
    {
        /**
         * @var \wpdb $wpdb ;
         */
        global $wpdb ;
        Utils::debug(__METHOD__);

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                'SELECT option_name, option_value FROM `'.$wpdb->options.'` WHERE option_name LIKE %s',
                self::WPOPT_JOB.'%'
            )
        );
        Utils::debug(__METHOD__, ['jobs in queue:'=>count($rows)]);
        foreach( $rows as $row )
        {
            $job = Job::jsonUnserialize( $row->option_value );
            //Utils::debug(__METHOD__, ['job'=>$job]);
            if( $this->job_lock( $job ) )
            {
                $this->runJob( $job );
                $this->job_unlock( $job );
                $this->job_cleanup( $job );
            }
            else
            {
                // Failed to lock the job.
                // @todo Managing some lifecycle stuff here ...
                Utils::debug(__METHOD__, '@todo: Failed to lock the job.');
            }
        }
    }

    public function statusUpdate( $jobKey, Array $status )
    {
        /**
         * @var \wpdb $wpdb ;
         */
        global $wpdb ;

        // key must by <= 191 bytes length because of `option_name VARCHAR(191)`.
        $key = self::WPOPT_JOB_STATUS . $jobKey ;
        $value = json_encode($status);

        $result = $wpdb->query(
            $wpdb->prepare(
                'REPLACE INTO `'.$wpdb->options.'` (`option_name`,`option_value`,`autoload`) VALUES (%s, %s, %s)',
                $key, $value, $autoload=false
            )
        );
    }

    public function statusGet( $jobKey )
    {
        /**
         * @var \wpdb $wpdb ;
         */
        global $wpdb ;

        // key must by <= 191 bytes length because of `option_name VARCHAR(191)`.
        $key = self::WPOPT_JOB_STATUS . $jobKey ;

        $row = $wpdb->get_row(
            $wpdb->prepare(
                'SELECT option_value FROM `'.$wpdb->options.'` WHERE option_name LIKE %s',
                $key
            )
        );
        $status = json_decode( $row->option_value, $assoc=true );

        return $status ;
    }

    /**
     * Undocumented function
     * @throws \InvalidArgumentException
     * @param Job $job
     * @return void
     */
    protected function runJob( Job $job )
    {
        $args = $job->getArgs() ;
        // Add the Job's key as the last argument.
        $args[] = $job->getKey();

        switch( $job->getType() )
        {
            case Job::TYPE_ACTION:
                \do_action( $job->getCallback(), $args );
                break;

            case Job::TYPE_CALLBACK:
                if( is_callable( $job->getCallback() ) )
                {
                    \call_user_func_array( $job->getCallback(), $args );
                }
                else
                {
                    // @todo Manage error for callback not found.
                    Utils::debug(__METHOD__, '@todo: Callback not found.');
                }
                break;
            default:
               throw new \InvalidArgumentException('Unknow type "'.$job->getType() ); 
        }
    }

    /**
     * Like table wp_options has unique index on option_name field,
     * we can try a insert and catch the exception if key already exists.
     * In my view it's the best solution with the tools at our disposal...
     *
     * @return boolean
     */
    protected function job_lock( Job $job )
    {
        /**
         * @var \wpdb $wpdb ;
         */
        global $wpdb ;

        // key must by <= 191 bytes length because of `option_name VARCHAR(191)`.
        $key = self::WPOPT_JOB_LOCK . $job->getKey() ;
        $value = time();

        $old_suppress_errors = $wpdb->suppress_errors ;
        $wpdb->suppress_errors = true ;

        $result = $wpdb->query(
            $wpdb->prepare(
                'INSERT INTO `'.$wpdb->options.'` (`option_name`,`option_value`,`autoload`) VALUES (%s, %s, %s)',
                $key, $value, $autoload=false
            )
        );

        $wpdb->suppress_errors = $old_suppress_errors ;

        if( empty($result) )
        {
            // May be: Duplicate entry '_wpqm-job-lock_05c2676c7f9f2d315d0d1c39a65bdb9e' for key 'option_name'.
            //Utils::debug(__METHOD__, ['result'=>$wpdb->last_error]);
            return false ;
        }
        return true ;
    }

    protected function job_unlock( Job $job )
    {
        /**
         * @var \wpdb $wpdb ;
         */
        global $wpdb ;

        $key = self::WPOPT_JOB_LOCK . $job->getKey() ;

        $result = $wpdb->query(
            $wpdb->prepare(
                'DELETE FROM `'.$wpdb->options.'` WHERE `option_name`=%s',
                $key
            )
        );
    }

    protected function job_cleanup( Job $job )
    {
        /**
         * @var \wpdb $wpdb ;
         */
        global $wpdb ;

        // Delete the job
        $key = self::WPOPT_JOB . $job->getKey() ;
        $result = $wpdb->query(
            $wpdb->prepare(
                'DELETE FROM `'.$wpdb->options.'` WHERE `option_name`=%s',
                $key
            )
        );

        // @todo Delete old status
    }
}
