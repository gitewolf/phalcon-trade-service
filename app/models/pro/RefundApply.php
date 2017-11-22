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
 * 退款申请表
 * @property int $id          //主键
 * @property int $uid          //用户oId
 * @property double $apply_amount          //申请退款金额
 * @property double $amount          //退款金额
 * @property int $type  //退款类型 1:根据订单  2: 根据消费
 * @property int $pay_type  //退款方式  1: 退款到资金账户, 2: 退款到支付来源, 3: 直接打款
 * @property int $status  // 1: 申请, 2 处理中, 3 已发出, 4 结束 -1 取消 -2 拒绝
 * @property int $trade_no  //渠道交易号
 * @property int $order_id  //相关订单 逗号隔开
 * @property string $reson         //拒绝理由
 * @property string $history         //记录每步的操作记录
 * @property string $operator   // 操作人
 * @property int $update_time
 * @property int $create_time
 */
class RefundApply extends Model
{
    function getSource()
    {
        return "jds_refund_apply";
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