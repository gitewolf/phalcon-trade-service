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
 * 用户代金券表
 * @property int $id          //主键
 * @property int $uid          //用户Id
 * @property int $order_id  //相关订单
 * @property int $sub_order_id  //相关子订单
 * @property int $code          //代金券码
 * @property int $plan_id   //计划ID
 * @property int $name   //代金券名称
 * @property int $status   //  1. 未绑定 2. 已绑定, 3 已使用完, 4 已过期
 * @property int $type   // 1: 导出券, 2. 代金券, 3 试用券
 * @property int $bind_time   //绑定时间
 * @property int $start_time   //开始生效时间     max(bind_time, 计划的start_time)
 * @property int $limit_time   //到期时间 绑定时间+有效期. 精确到天
 * @property int $amount  //代金券金额
 * @property int $balance  //剩余金额
 * @property int $update_time
 * @property int $create_time
 */
class UserCoupon extends Model
{
    const STATUS_INIT = 1;
    const STATUS_BIND =2 ;
    const STATUS_USED = 3;
    const STATUS_EXPIRED = 4;

    function getSource()
    {
        return "jds_user_coupon";
    }


    public function init(CouponPlan $plan, $uid){
//        var_dump($plan);exit;
        $this->plan_id = $plan->id;
        $this->name = $plan->name;
        $this->type = $plan->type;
        $this->start_time = $plan->start_time;
        if(!empty($uid)){
            $this->uid = $uid;
            $this->status = 2;
            $this->bind_time = time();
            $this->limit_time = $this->calcLimitTime($plan->valid_type,$plan->valid_period, $plan->end_time);
        }else{
            $this->status = 1;
        }
    }

    public function calcLimitTime($validType,$validPeriod,$planEndTime){
        switch($validType){
            case 1:
                $limitday =  date('Ymd',strtotime('+'.$validPeriod.'day',time()));
                break;
            case 2:
                $limitday =  date('Ymd',strtotime('+'.$validPeriod.'month',time()));
                break;
            case 3:
                $limitday =  date('Ymd',strtotime('+'.$validPeriod.'year',time()));
                break;
            default:
                $limitday = date('Ymd', time());

        }
        $limitTime = strtotime($limitday) + 86400 -1;
        if(!empty($planEndTime) && $limitTime > $planEndTime){
            $limitTime = $planEndTime;
        }
        return $limitTime;

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