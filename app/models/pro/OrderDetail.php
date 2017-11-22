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
 *  订单明细表
 * @property int $id
 * @property int $order_id      //订单编号
 * @property string $goods_id   //商品ID
 * @property string $goods_ver   //商品版本
 * @property string $goods_name   //商品名称
 * @property int $goods_type  // 商品类型 1: 充值,2: 导出
 * @property string $buyer_id   //买家Id
 * @property string $seller_id   //卖家ID
 * @property string $product_type  // 产品类型
 * @property string $matter_ids  // 素材或素材包. 多个素材时,逗号隔开
 * @property string $coupons  // 代金券计划ID+金额 json串
 * @property double $buy_price   //实际购买单价
 * @property int    $buy_num   //购买数量
 * @property double $total_price   //结算总价
 * @property int $status   // 订单状态 1: 跟随主订单  2:单独退货
 * @property string $additional_services  // 额外服务
 * @property int $update_time
 * @property int $create_time
 */
class OrderDetail extends Model
{

    const STATUS_INIT = 1;
    const STATUS_PAYED =2 ;
    const STATUS_SEND = 3;
    const STATUS_REFUND = 4;
    const STATUS_CANCEL = 5;
    const STATUS_CLOSE = 6;

    public function init($orderGoodsArr)
    {
        $this->goods_id = isset($orderGoodsArr['goods_id']) ? $orderGoodsArr['goods_id'] : 0;
        $this->goods_ver = isset($orderGoodsArr['goods_ver']) ? $orderGoodsArr['goods_ver'] : 0;
        $this->goods_name = isset($orderGoodsArr['goods_name']) ? $orderGoodsArr['goods_name'] : 0;
        $this->buy_num = isset($orderGoodsArr['buy_num']) ? $orderGoodsArr['buy_num'] : 0;
        $this->product_type = isset($orderGoodsArr['product_type']) ? $orderGoodsArr['product_type'] : 0;
        $this->goods_type = isset($orderGoodsArr['goods_type']) ? $orderGoodsArr['goods_type'] : 0;
        $this->matter_ids = isset($orderGoodsArr['matter_ids']) ? $orderGoodsArr['matter_ids'] : '';
        $this->coupons = isset($orderGoodsArr['coupons']) ? $orderGoodsArr['coupons'] : '';
        $this->buy_price = isset($orderGoodsArr['buy_price']) ? $orderGoodsArr['buy_price'] : 0;
        $this->seller_id = isset($orderGoodsArr['seller_id']) ? $orderGoodsArr['seller_id'] : 0;
        $this->buyer_id = isset($orderGoodsArr['buyer_id']) ? $orderGoodsArr['buyer_id'] : 0;
        $this->total_price = isset($orderGoodsArr['total_price']) ? $orderGoodsArr['total_price'] : 0;
        $this->status = isset($orderGoodsArr['status']) ? $orderGoodsArr['status'] : 0;
        $this->additional_services = isset($orderGoodsArr['additional_services']) ? $orderGoodsArr['additional_services'] : '';

    }

    function getSource()
    {
        return "jds_order_detail";
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