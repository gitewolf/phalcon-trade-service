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
 * 用户消息表
 * @property int $id          //ID
 * @property int $type          //消息类型 1:系统消息, 2:活动公告
 * @property string $uid   //用户ID, 当活动公告时
 * @property string $mass_type   //群发类型, 0:所有人, 1:专业版 2:教育版
 * @property string $title   //消息标题
 * @property string $content   // 消息内容
 * @property int $is_delete   //是否删除
 * @property int $is_read   // 是否已读
 * @property int $create_time
 * @property int $update_time
 */



class Message extends Model
{

    const TYPE_SYSTEM = 1;
    const TYPE_MASS = 2;

    const MASS_ALL = 0;
    const MASS_PRO = 1;

    function getSource()
    {
        return "jds_message";
    }



    public function beforeUpdate()
    {
        $this->update_time = time();
    }

    public function beforeCreate()
    {
        $this->create_time = time();
        $this->update_time = time();
    }
}