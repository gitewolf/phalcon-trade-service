<?php
/**
 * Created by PhpStorm.
 * User: jacky
 * Date: 17-9-17
 * Time: 上午10:47
 */

namespace Services;


use Common\ErrorCode;
use Exceptions\PikException;

use Models\Pro\Order;
use Payment\Client\Query;
use Payment\Common\PayException;
use Payment\Client\Charge;
use Payment\Config;

class PayService extends BaseService
{
    private $aliConfig = '';

    public function __construct()
    {
        parent::__construct();
        $this->aliConfig = json_decode(json_encode($this->getDi()->get('config')->aliPay), true);

    }

    /**
     * 获取阿里支付二维码
     * @param Order $order
     * @return mixed
     * @throws PikException
     */
    public function getAliPayQrcode($order)
    {
        if (empty($order)) {
            throw new PikException(ErrorCode::ORDER_NOT_EXIST);
        }
        if ($order->status != 1) {
            throw new PikException(ErrorCode::ORDER_CANNOT_TO_PAY);
        }

        $payData = [
            'body' => '订单支付宝扫码支付',
            'subject' => '订单支付',
            'order_no' => $order->order_id,
            'timeout_express' => time() + 600,// 表示必须 600s 内付款
            'amount' => $order->pay_money,// 单位为元 ,最小为0.01
            'return_param' => '123123',
//            'client_ip' => $clientIp,// 客户地址
            'goods_type' => '0',// 0—虚拟类商品，1—实物类商品
            'store_id' => '',   //门店号
            'operator_id' => '',
            'terminal_id' => '',// 终端设备号(门店号或收银设备ID) 默认值 web
        ];

        try {
            $str = Charge::run(Config::ALI_CHANNEL_QR, $this->aliConfig, $payData);
            return $str;
        } catch (PayException $e) {
            var_dump($e);
            throw new PikException(ErrorCode::PAY_CHARGE_GET_QR_ERROR);
        }
    }


    /**
     * 获取阿里支付二维码
     * @param $orderId
     * @param $trade_no
     * @return mixed
     * @throws PikException
     */
    public function queryAliTradeDetail($orderId, $trade_no = "")
    {
        $queryData = [];
        if (!empty($orderId)) {
            $queryData['out_trade_no'] = $orderId;
        } else {
            $queryData['trade_no'] = $trade_no;
        }
        try {
            $str = Query::run(Config::ALI_CHARGE, $this->aliConfig, $queryData);
        } catch (PayException $e) {
            echo $e->errorMessage();
            exit;
        }
        return $str;// 这里如果直接输出到页面，&not 会被转义，请注意
    }


    /**
     * 获取阿里支付二维码
     * @param Order $order
     * @param $clientIp
     * @return mixed
     * @throws PikException
     */
    public function getAliPayWebUrl($order)
    {
        if (empty($order)) {
            throw new PikException(ErrorCode::ORDER_NOT_EXIST);
        }
        if ($order->status != 1) {
            throw new PikException(ErrorCode::ORDER_CANNOT_TO_PAY);
        }

        $payData = [
            'body' => '订单支付宝扫码支付',
            'subject' => '订单支付',
            'order_no' => $order->order_id,
            'timeout_express' => time() + 600,// 表示必须 600s 内付款
            'amount' => $order->pay_money,// 单位为元 ,最小为0.01
            'return_param' => '123123',
            'goods_type' => '0',// 0—虚拟类商品，1—实物类商品
            'store_id' => '',   //门店号
            'qr_mod' => '',
        ];
        try {
            $str = Charge::run(Config::ALI_CHANNEL_WEB, $this->aliConfig, $payData);
        } catch (PayException $e) {
            echo $e->errorMessage();
            exit;
        }
        file_put_contents("/tmp/sanbox.log", $str. "\n", FILE_APPEND);
        return $str;// 这里如果直接输出到页面，&not 会被转义，请注意
    }



}