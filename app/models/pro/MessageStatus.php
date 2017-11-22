<?php
/**
 * Created by PhpStorm.
 * User: jacky
 * Date: 17-8-15
 * Time: 下午4:17
 */

namespace Models\Pro;

use Phalcon\Mvc\Model;

/**
 * 公告是否已读
 * @property int $id          //ID
 * @property int $msg_id          //消息ID
 * @property string $uid   //用户ID, 当活动公告时
 * @property int $create_time
 */
class MessageStatus extends Model
{
    function getSource()
    {
        return "jds_message_status";
    }
    public function beforeCreate()
    {
        $this->create_time = time();
    }
}