<?php


namespace Models\Redis;

/**
 * Created by PhpStorm.
 * User: jacky
 * Date: 17-8-5
 * Time: 下午6:15
 */
class UploadTask extends RedisBase
{
    private $task = 'MQ_CRON_PRO_UPLOAD';


    public function addTask($planTime, $task)
    {
        return $this->db->zAdd($this->task, $planTime, json_encode($task) );

    }
}