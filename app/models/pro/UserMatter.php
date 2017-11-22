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
 * 用户拥有素材表
 * @property int $id          //主键
 * @property int $uid          //用户Id
 * @property string $matter_id          //素材ID
 * @property string $type          //素材类型
 * @property int $order_id  //相关订单
 * @property int $sub_order_id  //相关子订单
 * @property int $status  //上架状态 1: 上架, 2 下架
 * @property int $update_time
 * @property int $create_time
 */
class UserMatterPlan extends Model
{
    function getSource()
    {
        return "jds_user_matter";
    }


    protected function beforeUpdate()
    {
        $this->update_time = time();
    }
    protected function beforeCreate()
    {
        $this->create_time = time();
        $this->update_time = time();
    }
}