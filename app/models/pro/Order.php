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
 * 订单表
 * @property int $order_id          //订单编号 16位
 * @property string $buyer_id   //买家Id
 * @property string $buyer_name   //买家昵称
 * @property string $seller_id   //卖家ID
 * @property string $seller_name   //卖家昵称
 * @property int $seller_type   // 卖家类型 1. 用户,  2 官方
 * @property int $type  //      类型 1: 充值,2: 导出 3: 购买产品
 * @property int $pay_type   // 支付方式 1.现金, 2 皮币
 * @property string $pay_channel  // 支付渠道
 * @property string $pay_trade_no  //渠道交易单号
 * @property double $order_base_money   //订单原价总金额
 * @property double $order_money   //订单总金额
 * @property double $pay_money   //充值并支付金额
 * @property double $pay_coin   //使用皮币余额
 * @property double $pay_coupon   //使用优惠券金额
 * @property string $pay_coupon_detail   //使用的优惠券的code和对应金额json
 *
 * @property int $status   // 订单状态 1 待支付, 2 已支付, 3 已发货 4 已退货 5 取消, 6关闭
 * @property int $refund_goods_status   // 退货状态 0, 不需退货 1 待退货, 2 退货完成
 * @property int $refund_money_status   // 退款状态 0, 不需退款 1 待退款, 2 退款完成
 * @property string $remark    //备注
 * @property int $pay_time  //支付时间
 * @property int $invoice_status  //1: 未开发票 2:正在开发票3:已开发票
 * @property string $export_info  // 导出订单的对应信息json串
 * @property int $update_time
 * @property int $create_time
 */
class Order extends Model
{
    const STATUS_INIT = 1;
    const STATUS_PAYED =2 ;
    const STATUS_SEND = 3;
    const STATUS_REFUND = 4;
    const STATUS_CANCEL = 5;
    const STATUS_CLOSE = 6;

    const PAY_TYPE_MONEY = 1;
    const PAY_TYPE_COIN = 2;

    const SELL_TYPE_SYS = 1;
    const SELL_TYPE_USER = 2;

    const TYPE_CHARGE = 1;
    const TYPE_EXPORT = 2;
    const TYPE_BUY = 3;

    const  REFUND_MONEY_STATUS_INIT = 0;
    const  REFUND_MONEY_STATUS_PROCESS = 1;
    const  REFUND_MONEY_STATUS_FINISHED = 2;




    function getSource()
    {
        return "jds_order";
    }

    public function init($orderArr)
    {
        $this->buyer_id = isset($orderArr['buyer_id']) ? $orderArr['buyer_id'] : '';
        $this->buyer_name = isset($orderArr['buyer_name']) ? $orderArr['buyer_name'] : '';
        $this->seller_id = isset($orderArr['seller_id']) ? $orderArr['seller_id'] : '';
        $this->seller_name  = isset($orderArr['seller_name ']) ? $orderArr['seller_name '] : '';
        $this->seller_type  = isset($orderArr['seller_type ']) ? $orderArr['seller_type '] : 1;
        $this->type = isset($orderArr['type']) ? $orderArr['type'] : 0;
        $this->pay_type = isset($orderArr['pay_type']) ? $orderArr['pay_type'] : 0;
        $this->pay_channel = isset($orderArr['pay_channel']) ? $orderArr['pay_channel'] : '';
        $this->order_money = isset($orderArr['order_money']) ? $orderArr['order_money'] : 0;
        $this->order_base_money = isset($orderArr['order_base_money']) ? $orderArr['order_base_money'] : 0;
        $this->pay_money = isset($orderArr['pay_money']) ? $orderArr['pay_money'] : 0;
        $this->pay_coin = isset($orderArr['pay_coin']) ? $orderArr['pay_coin'] : 0;
        $this->pay_coupon = isset($orderArr['pay_coupon']) ? $orderArr['pay_coupon'] : '';
        $this->pay_coupon_detail = isset($orderArr['pay_coupon_detail']) ? $orderArr['pay_coupon_detail'] : '';
        $this->export_info  = isset($orderArr['export_info']) ? $orderArr['export_info'] : '';
        $this->remark = isset($orderArr['remark']) ? $orderArr['remark'] : '';

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