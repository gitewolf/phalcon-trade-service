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
 * 支付表
 * @property int $pay_id          //订单编号 16位
 * @property string $buyer_id   //买家Id
 * @property string $buyer_name   //买家昵称
 * @property int $pay_type   // 支付方式 1.皮币, 2现金
 * @property string $pay_channel  // 支付渠道
 * @property string $pay_trade_no  //渠道交易单号
 * @property double $pay_money   //订单金额
 * @property double $order_id   //订单ID
 * @property int $status   // 支付状态 1 待支付, 2 已支付成功
 * @property string $remark    //备注
 * @property int $pay_time  //支付时间
 * @property int $update_time
 * @property int $create_time
 */
class PayRequest extends Model
{
    const STATUS_INIT = 1;
    const STATUS_PAYED =2 ;




    function getSource()
    {
        //暂时不需要
        return "jds_pay_request";
    }

    public function init($payInfo)
    {
        $this->buyer_id = isset($payInfo['buyer_id']) ? $payInfo['buyer_id'] : '';
        $this->buyer_name = isset($payInfo['buyer_name']) ? $payInfo['buyer_name'] : '';
        $this->order_id = isset($payInfo['order_id']) ? $payInfo['order_id'] : '';
        $this->pay_type = isset($payInfo['pay_type']) ? $payInfo['pay_type'] : 0;
        $this->pay_channel = isset($payInfo['pay_channel']) ? $payInfo['pay_channel'] : '';
        $this->pay_trade_no = isset($payInfo['pay_trade_no']) ? $payInfo['pay_trade_no'] : '';
        $this->pay_money = isset($payInfo['pay_money']) ? $payInfo['pay_money'] : 0;
        $this->pay_time = isset($payInfo['pay_time']) ? $payInfo['pay_time'] : '';
        $this->remark = isset($payInfo['remark']) ? $payInfo['remark'] : '';

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