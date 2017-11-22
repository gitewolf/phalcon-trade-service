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
 * 代金券明细
 * @property int $code          //代金券码 按照规则生成
 * @property int $plan_id   //计划ID
 * @property int $status   //  1:未绑定, 2. 已绑定, 3 已使用, 4 已过期
 * @property int $type   // 1: 导出券, 2. 代金券, 3 试用券
 * @property string $bind_uid   //绑定用户id
 * @property string $bind_username   //绑定用户名
 * @property int $bind_time   //绑定时间
 * @property int $start_time   //开始生效时间     max(bind_time, 计划的start_time)
 * @property int $limit_time   //到期时间 绑定时间+有效期. 精确到天
 * @property int $amount  //代金券金额
 * @property int $update_time
 * @property int $create_time
 */
class Coupon extends Model
{
    function getSource()
    {
        return "jds_coupon";
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