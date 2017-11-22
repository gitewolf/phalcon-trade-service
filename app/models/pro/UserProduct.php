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
 * 用户拥有产品服务表
 * @property int $id          //主键
 * @property int $uid          //用户Id
 * @property string $product_type          //产品类型
 * @property int $order_id  //相关订单
 * @property int $sub_order_id  //相关子订单
 * @property string $additional_services   // 额外服务
 * @property int $limit_time   //到期时间
 * @property int $update_time
 * @property int $create_time
 */
class UserProduct extends Model
{
    function getSource()
    {
        return "jds_user_product";
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