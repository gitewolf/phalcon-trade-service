<?php

namespace Models\Redis;

use Phalcon\Di;

class RedisBase
{
    /** @var  $db \Redis*/
    protected $db = null;

    public function __construct($dbRes = null)
    {
        if (!empty($dbRes)) {
            $this->db = $dbRes;
        } else {
            $this->db = Di::getDefault()->getShared('redis');
        }
    }//end

}//end class

