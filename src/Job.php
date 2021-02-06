<?php
namespace Cyrille37\WPQueueManager ;

class Job implements \JsonSerializable
{
    /**
     * Wordpress action tag.
     */
    const TYPE_ACTION = 'action';
    /**
     * A callable as call_user_func_array().
     * https://www.php.net/manual/en/function.call-user-func-array
     */
    const TYPE_CALLBACK = 'callback';
    /**
     * All know types.
     */
    const TYPES = [self::TYPE_ACTION, self::TYPE_CALLBACK];

    protected $type ;
    protected $callback ;
    protected $key ;
    protected $args ;
    protected $created_at ;

    /**
     * Undocumented function
     *
     * @param string $type Must be in self::TYPES
     * @param string|array $callback The call name for (do_action) or callable (call_user_func_array)
     * @param Array|null $args
     * @param integer|null $created_at
     * @return Job
     */
    public function __construct( $type, $callback, Array $args=null, $created_at=null )
    {
        if( ! in_array($type, self::TYPES) )
            throw new \InvalidArgumentException('Unknow type:"'.$type.'".');
        if( empty($callback) )
            throw new \InvalidArgumentException('Invalid empty callback.');

        $this->type = $type ;
        $this->callback = $callback ;
        $this->args = $args ;
        $this->key = $this->computeKey();
        if( empty($created_at) )
            $this->created_at = time() ;
        else
            $this->created_at = $created_at ;
    }

    /**
     * To serialize protected attributes.
     * @see \JsonSerializable
     * @return array
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }

    /**     *
     * @param string $json
     * @return Job
     */
    public static function jsonUnserialize( string $json )
    {
        $obj = json_decode($json, $assoc=true);
        $job = new Job(
            $obj['type'],
            $obj['callback'],
            $obj['args'],
            $obj['created_at'],
        );
        $job->key = $obj['key'] ;
        return $job ;
    }

    /**
     * @return string
     */
    protected function computeKey()
    {
        return md5( microtime(true).rand(0,100000) );
    }

    public function getKey()
    {
        return $this->key ;
    }

    public function getArgs()
    {
        return $this->args ;
    }

    public function getType()
    {
        return $this->type ;
    }

    public function getCallback()
    {
        return $this->callback ;
    }
}